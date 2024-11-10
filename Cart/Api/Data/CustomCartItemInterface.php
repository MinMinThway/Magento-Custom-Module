<?php

namespace MMT\Cart\Api\Data;

use Magento\Quote\Api\Data\CartItemInterface;

interface CustomCartItemInterface extends CartItemInterface
{

    const KEY_PRODUCT_IMG_PATH = 'img_path';
    const KEY_SUBTOTAL = 'subtotal';
    const KEY_ATTRIBUTES = 'attributes';
    const KEY_PARENT_ID = 'parent_id';
    const KEY_PRODUCT_ID = 'product_id';
    const KEY_IS_POINT_PRODUCT = 'is_point_product';
    const KEY_POINT_LABEL = 'point_txt';

    /**
     * set image path for product in cart
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get image path for product in cart
     * @return string
     */
    public function getImgPath();

    /**
     * set subtotal for product in cart
     * @param float $subtotal
     * @return $this
     */
    public function setSubTotal($subtotal);

    /**
     * get subtotal for product in cart
     * @return float
     */
    public function getSubTotal();

    /**
     * set attributes for product in cart
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     */
    public function setAttributes($attributes);

    /**
     * get attributes for product in cart
     * @return \Magento\Framework\Api\AttributeInterface[]|[]
     */
    public function getAttributes();

    /**
     * set parent id of product if configurable product
     * if not, set self id
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * get parent id of product if configurable product
     * if not, get self id
     * @return int
     */
    public function getParentId();

    /**
     * set product id
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * get product id
     * @return int
     */
    public function getProductId();

    /**
     * set is point product
     * @param bool $isPointProduct
     * @return $this
     */
    public function setIsPointProduct($isPointProduct);

    /**
     * get is point product
     * @return bool
     */
    public function getIsPointProduct();

    /**
     * set point label
     * @param string $pointLabel
     * @return $this
     */
    public function setPointLabel($pointLabel);

    /**
     * get point label
     * @return string
     */
    public function getPointLabel();
}

