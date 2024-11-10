<?php

namespace MMT\Cart\Model\Cart;

use Magento\Quote\Model\Cart\Totals;
use MMT\Cart\Api\Data\CustomTotalsInterface;

class CustomTotals extends Totals implements CustomTotalsInterface
{
    /**
     * @inheritdoc
     */
    public function setShippingAddress($shippingAddress)
    {
        return $this->setData(self::KEY_CUSTOM_SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * @inheritdoc
     */
    public function getShippingAddress()
    {
        return $this->getData(self::KEY_CUSTOM_SHIPPING_ADDRESS);
    }

    /**
     * Get totals by items
     *
     * @return \MMT\Cart\Api\Data\CustomTotalsItemInterface[]|null
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Get totals by items
     *
     * @param \MMT\Cart\Api\Data\CustomTotalsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function setShowGiftProduct($isShowGift)
    {
        return $this->setData(self::KEY_SHOW_GIFT_PRODUCT, $isShowGift);
    }

    /**
     * @inheritdoc
     */
    public function getShowGiftProduct()
    {
        return $this->getData(self::KEY_SHOW_GIFT_PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function setCartId($cartId)
    {
        return $this->setData(self::KEY_CART_ID, $cartId);
    }

    /**
     * @inheritdoc
     */
    public function getCartId()
    {
        return $this->getData(self::KEY_CART_ID);
    }
}
