<?php

namespace MMT\Cart\Api\Data;

use Magento\Quote\Api\Data\TotalsInterface;

interface CustomTotalsInterface extends TotalsInterface
{

    const KEY_CUSTOM_SHIPPING_ADDRESS = 'customshippingaddress';
    const KEY_SHOW_GIFT_PRODUCT = 'showgiftproduct';
    const KEY_CART_ID = 'cartid';

    /**
     * set shipping address
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress($shippingAddress);

    /**
     * get shipping address
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getShippingAddress();

    /**
     * Get totals by items
     *
     * @return \MMT\Cart\Api\Data\CustomCartItemInterface[]|null
     */
    public function getItems();

    /**
     * Set totals by items
     *
     * @param \MMT\Cart\Api\Data\CustomCartItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * Set showGiftProduct
     * 
     * @param bool $isShowGift
     * @return $this
     */
    public function setShowGiftProduct($isShowGift);

    /**
     * Get showGiftProduct
     * 
     * @return bool
     */
    public function getShowGiftProduct();

    /**
     * Set cartId
     * 
     * @param int $cartId
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * Get cartId
     * 
     * @return int $cartId
     */
    public function getCartId();
}
