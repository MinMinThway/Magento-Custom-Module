<?php
namespace MMT\Checkout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Checkout\Api\Data\ShippingCarrierInterface;

class ShippingCarrier extends AbstractExtensibleModel implements ShippingCarrierInterface
{
    /**
     * @inheritDoc
     */
    public function getShippingCarrierCode()
    {
        return $this->getData(self::SHIPPING_CARRIER_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setShippingCarrierCode($carrier_code)
    {
        return $this->setData(self::SHIPPING_CARRIER_CODE, $carrier_code);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodCode()
    {
        return $this->getData(self::SHIPPING_METHOD_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethodCode($carrier_code)
    {
        return $this->setData(self::SHIPPING_METHOD_CODE, $carrier_code);
    }
}