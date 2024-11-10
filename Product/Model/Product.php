<?php

namespace MMT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Product\Api\Data\ProductInterface;

class Product extends AbstractExtensibleModel implements ProductInterface
{

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }


    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountPrice()
    {
        return $this->getData(self::DISCOUNT_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountPrice($discountPrice)
    {
        return $this->setData(self::DISCOUNT_PRICE, $discountPrice);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::DISCOUNT_LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountLabel($discountLabel)
    {
        return $this->setData(self::DISCOUNT_LABEL, $discountLabel);
    }

    /**
     * @inheritdoc
     */
    public function getMpRewardPoints()
    {
        return $this->getData(self::MP_REWARD_POINTS);
    }

    /**
     * @inheritdoc
     */
    public function setMpRewardPoints($mpRewardPoints)
    {
        return $this->setData(self::MP_REWARD_POINTS, $mpRewardPoints);
    }

    /**
     * @inheritdoc
     */
    public function getMpReviewPoints()
    {
        return $this->getData(self::MP_REVIEW_POINTS);
    }

    /**
     * @inheritdoc
     */
    public function setMpReviewPoints($mpReviewPoints)
    {
        return $this->setData(self::MP_REVIEW_POINTS, $mpReviewPoints);
    }

    /**
     * @inheritdoc
     */
    public function getIsFreeShipping()
    {
        return $this->getData(self::IS_FREE_SHIPPING);
    }

    /**
     * @inheritdoc
     */
    public function setIsFreeShipping($isFreeShipping)
    {
        return $this->setData(self::IS_FREE_SHIPPING, $isFreeShipping);
    }

    /**
     * @inheritdoc
     */
    public function getFreeShippingImg()
    {
        return $this->getData(self::FREE_SHIPPING_IMG);
    }

    /**
     * @inheritdoc
     */
    public function setFreeShippingImg($freeShippingImg)
    {
        return $this->setData(self::FREE_SHIPPING_IMG, $freeShippingImg);
    }

    /**
     * @inheritdoc
     */
    public function setIsPointProduct($isPointProduct)
    {
        return $this->setData(self::IS_POINT_PRODUCT, $isPointProduct);
    }

    /**
     * @inheritdoc
     */
    public function getIsPointProduct()
    {
        return $this->getData(self::IS_POINT_PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function setPointLabel($pointLabel)
    {
        return $this->setData(self::POINT_LABEL, $pointLabel);
    }

    /**
     * @inheritdoc
     */
    public function getPointLabel()
    {
        return $this->getData(self::POINT_LABEL);
    }
}
