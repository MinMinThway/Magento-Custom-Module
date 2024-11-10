<?php
namespace MMT\Checkout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Checkout\Api\Data\ShippingAndBillingAddressInterface;

class ShippingAndBillingAddress extends AbstractExtensibleModel implements ShippingAndBillingAddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        return $this->setData(self::SHIPPING_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        return $this->getData(self::BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        return $this->setData(self::BILLING_ADDRESS, $address);
    }

}