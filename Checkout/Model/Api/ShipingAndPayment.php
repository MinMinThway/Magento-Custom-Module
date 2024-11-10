<?php
namespace MMT\Checkout\Model\Api;

use MMT\Checkout\Api\ShipingAndPaymentInterface;
use Magento\Quote\Api\Data\PaymentInterface;

use Magento\Quote\Model\ShippingMethodManagement;
use MMT\Checkout\Model\CustomShippingAndPaymentMethodFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use MMT\Checkout\Api\Data\ShippingCarrierInterface;
use Magento\Sales\Model\OrderRepository;
use MMT\Checkout\Api\Data\CheckoutResultInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Checkout\Model\PaymentInformationManagement as ModelPaymentInformationManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class ShipingAndPayment implements ShipingAndPaymentInterface
{

    /**
     * @var ShippingMethodManagement
     */
    protected $shippingMethodManagement;

    /**
     * @var CustomShippingAndPaymentMethodFactory
     */
    protected $customShippingAndPaymentMethodFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ShippingInformationManagement
     */
    protected $shippingInformationManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CheckoutResultInterfaceFactory
     */
    private $checkoutResultInterfaceFactory;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var ModelPaymentInformationManagement
     */
    protected $paymentInformationManagement;

    /** 
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;
    public function __construct(
        ShippingMethodManagement $shippingMethodManagement,
        CustomShippingAndPaymentMethodFactory $customShippingAndPaymentMethodFactory,
        CartRepositoryInterface $quoteRepository,
        ShippingInformationManagement $shippingInformationManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartRepositoryInterface $cartTotalsRepository,
        Logger $logger,
        QuoteAddressValidator $addressValidator,
        OrderRepository $orderRepository,
        CheckoutResultInterfaceFactory $checkoutResultInterfaceFactory,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null,
        ModelPaymentInformationManagement $paymentInformationManagement,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->customShippingAndPaymentMethodFactory = $customShippingAndPaymentMethodFactory;
        $this->quoteRepository = $quoteRepository;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->logger = $logger;
        $this->addressValidator = $addressValidator;
        $this->orderRepository = $orderRepository;
        $this->checkoutResultInterfaceFactory = $checkoutResultInterfaceFactory;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()->get(CartExtensionFactory::class);
        $this->shippingAssignmentFactory = $shippingAssignmentFactory ?: ObjectManager::getInstance()->get(ShippingAssignmentFactory::class);
        $this->shippingFactory = $shippingFactory ?: ObjectManager::getInstance()->get(ShippingFactory::class);
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getCustomShippingAndPaymentMethod($cartId, \MMT\Checkout\Api\Data\ShippingAndBillingAddressInterface $addressInformation)
    {
        $shippingAddress = $addressInformation->getShippingAddress();
        $shipping = $this->shippingMethodManagement->estimateByExtendedAddress($cartId, $shippingAddress);
        $payment = $this->getPaymentDeatails($cartId, $addressInformation);

        $shippingAndPayment = $this->customShippingAndPaymentMethodFactory->create();
        $shippingAndPayment->setPaymentMethods($payment->getPaymentMethods());
        $shippingAndPayment->setShippingMethods($shipping);
        return $shippingAndPayment;
    }


    public function getPaymentDeatails(
        $cartId,
        $addressInformation
    ) {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);

        $address = $addressInformation->getShippingAddress();
        $this->validateAddress($address);

        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        try {
            $billingAddress = $addressInformation->getBillingAddress();
            if ($billingAddress) {
                if (!$billingAddress->getCustomerAddressId()) {
                    $billingAddress->setCustomerAddressId(null);
                }
                $this->addressValidator->validateForCart($quote, $billingAddress);
                $quote->setBillingAddress($billingAddress);
            }

            $this->addressValidator->validateForCart($quote, $address);
            $quote->setIsMultiShipping(false);

            $this->quoteRepository->save($quote);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            throw new InputException(
                __(
                    'The shipping information was unable to be saved. Error: "%message"',
                    ['message' => $e->getMessage()]
                )
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }


        /** @var PaymentDetailsInterface $paymentDetails */

        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }

    /**
     * Validate quote
     *
     * @param Quote $quote
     * @throws InputException
     * @return void
     */
    protected function validateQuote(Quote $quote): void
    {
        if (!$quote->getItemsCount()) {
            throw new InputException(
                __('The shipping method can\'t be set for an empty cart. Add an item to cart and try again.')
            );
        }
    }

    /**
     * Validate shipping address
     *
     * @param AddressInterface|null $address
     * @return void
     * @throws StateException
     */
    private function validateAddress(?AddressInterface $address): void
    {
        if (!$address || !$address->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
    }


    /**
     * @inheritDoc
     */
    public function savePaymentInfoAndPlaceOrder($cartId, PaymentInterface $paymentMethod, ?AddressInterface $billingAddress = null, ShippingCarrierInterface $shipping)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $quote = $this->prepareShippingAssignment($quote, $billingAddress, $shipping->getShippingCarrierCode() . '_' . $shipping->getShippingMethodCode());
        $quote->setIsMultiShipping(false);
        $this->quoteRepository->save($quote);
        $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect((['increment_id', 'customer_email']))
            ->addFieldToFilter('entity_id', $orderId);
        $order = $orderCollection->getFirstItem();
        $result = $this->checkoutResultInterfaceFactory->create()->setEntityId($orderId)->setIncrementId($order->getIncrementId())->setEmail($order->getCustomerEmail());
        return $result;
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string $method
     * @return CartInterface
     */
    public function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method): CartInterface
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }

}