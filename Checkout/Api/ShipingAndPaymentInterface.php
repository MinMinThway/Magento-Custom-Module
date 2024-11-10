<?php
namespace MMT\Checkout\Api;

use MMT\Checkout\Api\Data\CheckoutResultInterface;

interface ShipingAndPaymentInterface
{
    /**
     * @param int $cartId
     * @param \MMT\Checkout\Api\Data\ShippingAndBillingAddressInterface $addressInformation
     * @return \MMT\Checkout\Api\Data\CustomShippingAndPaymentMethodInterface
     */
    public function getCustomShippingAndPaymentMethod(
        $cartId,
        \MMT\Checkout\Api\Data\ShippingAndBillingAddressInterface $addressInformation
    ); /**
      * Set payment information and place order for a specified cart.
      *
      * @param int $cartId
      * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
      * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
      * @param \MMT\Checkout\Api\Data\ShippingCarrierInterface $shipping
      * @throws \Magento\Framework\Exception\CouldNotSaveException
      * @return CheckoutResultInterface
      */
    public function savePaymentInfoAndPlaceOrder($cartId, \Magento\Quote\Api\Data\PaymentInterface $paymentMethod, \Magento\Quote\Api\Data\AddressInterface $billingAddress = null, \MMT\Checkout\Api\Data\ShippingCarrierInterface $shipping);
}