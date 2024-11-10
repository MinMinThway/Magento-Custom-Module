<?php

namespace MMT\Product\Api\Data;

interface SimpleProductInterface {
    const ID = 'id';
    const NAME = 'name';
    const SKU = 'sku';
    const PRICE = 'price';
    const DISCOUNT_PRICE = 'discount_price';
    const IMAGE = 'image';
    const DISCOUNT_LABEL = 'discount_label';
    const MP_REWARD_POINTS = 'mp_reward_points';
    const MP_REVIEW_POINTS = 'mp_review_points';
    const IS_FREE_SHIPPING = 'is_free_shipping';
    const FREE_SHIPPING_IMG = 'free_shipping_img';
    const STOCK_QTY = 'stock_qty';
    const ATTRIBUTES = 'attributes';
    const CONFIGURABLE_ATTRIBUTES = 'configurable_attributes';

    /**
     * get id
     * @return int
     */
    public function getId();

    /**
     * set id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * get name
     * @return string
     */
    public function getName();

    /**
     * set name
     * @param string $name
     * @return $this
     */
    public function setName($name);


    /**
     * get sku
     * @return string
     */
    public function getSku();

    /**
     * set sku
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * get price
     * @return float
     */
    public function getPrice();

    /**
     * set price
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * get discount price
     * @return float
     */
    public function getDiscountPrice();

    /**
     * set discount price
     * @param float $discountPrice
     * @return $this
     */
    public function setDiscountPrice($discountPrice);

    /**
     * get image
     * @return string
     */
    public function getImage();

    /**
     * set image
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * get discount label
     * @return string
     */
    public function getDiscountLabel();

    /**
     * set discount label
     * @param string $discountLabel
     * @return $this
     */
    public function setDiscountLabel($discountLabel);

    /**
     * get mp reward points
     * @return int
     */
    public function getMpRewardPoints();

    /**
     * set mp reward points
     * @param int $mpRewardPoints
     * @return $this
     */
    public function setMpRewardPoints($mpRewardPoints);

    /**
     * get mp review points
     * @return int
     */
    public function getMpReviewPoints();

    /**
     * set mp review points
     * @param int $mpReviewPoints
     * @return $this
     */
    public function setMpReviewPoints($mpReviewPoints);

    /**
     * get is freeshipping
     * @return bool
     */
    public function getIsFreeShipping();

    /**
     * set is freeshipping
     * @param bool $isFreeShipping
     * @return $this
     */
    public function setIsFreeShipping($isFreeShipping);

    /**
     * get freeshipping img
     * @return string
     */
    public function getFreeShippingImg();

    /**
     * set freeshipping img
     * @param string $freeShippingImg
     * @return $this
     */
    public function setFreeShippingImg($freeShippingImg);

    /**
     * get stock qty
     * @return int
     */
    public function getStockQty();

    /**
     * set stock qty
     * @param $stockQty
     * @return $this
     */
    public function setStockQty($stockQty);

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getAttributes();

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setAttributes(array $attributes);


    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getConfigurableAttributes();

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setConfigurableAttributes(array $configurableAttributes);




}