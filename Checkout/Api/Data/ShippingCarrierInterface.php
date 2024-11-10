<?php
namespace MMT\Checkout\Api\Data;

interface ShippingCarrierInterface
{
    const SHIPPING_CARRIER_CODE = "shipping_carrier_code";
    const SHIPPING_METHOD_CODE = "shipping_method_code";

    /**
     * Return shipping carrier code
     * @return string
     */
    public function getShippingCarrierCode();

    /**
     * Set shipping carrier code
     * @param string $carrier_code
     * @return $this
     */
    public function setShippingCarrierCode($carrier_code);

    /**
     * Return shipping method code
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method code
     * @param string $method_code
     * @return $this
     */
    public function setShippingMethodCode($method_code);
}