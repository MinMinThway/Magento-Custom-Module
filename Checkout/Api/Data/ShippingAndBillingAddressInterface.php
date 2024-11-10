<?php

namespace MMT\Checkout\Api\Data;

interface ShippingAndBillingAddressInterface
{
    const SHIPPING_ADDRESS = 'shipping_address';

    const BILLING_ADDRESS = 'billing_address';

    /**
     * Returns shipping address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns billing address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set billing address if additional synchronization needed
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $address);
}