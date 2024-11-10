<?php
namespace MMT\Checkout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Checkout\Api\Data\CustomShippingAndPaymentMethodInterface;

class CustomShippingAndPaymentMethod extends AbstractExtensibleModel implements CustomShippingAndPaymentMethodInterface
{
    /**
     * @inheritDoc
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }
}