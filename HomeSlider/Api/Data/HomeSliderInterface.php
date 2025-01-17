<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MMT\HomeSlider\Api\Data;



interface HomeSliderInterface
{

    const HOMESLIDER_ID = 'homeslider_id';
    const NAME = 'name';
    const WEBSITE_ID = 'website_id';
    const HOME_SLIDER_IMAGE = 'home_slider_image';
    const HOME_SLIDER_IMAGE_MOBILE = 'home_slider_image_mobile';
    const HOME_SLIDER_IMAGE_MOBILE_APP = 'home_slider_image_mobile_app';
    const CATEGORY_ID = 'category_id';
    const IS_ACTIVE = 'is_active';
    const IS_HOME_SLIDER = 'is_home_slider';
    const IS_HOME_SLIDER_ONE = 'is_home_slider_one';
    const PERCENTAGE_NAME = 'percentage_name';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const BANNER_IMAGE = 'banner_image';

    /**
     * Set homeslider_id
     * @param string $homesliderId
     * @return \MMT\HomeSlider\HomeSlider\Api\Data\HomeSliderInterface
     */
    public function setHomesliderId($homesliderId);

    /**
     * Get homeslider_id
     * @return string|null
     */
    public function getHomesliderId();


    /**
     * Set Slider
     * @param string $name
     * @return \MMT\HomeSlider\HomeSlider\Api\Data\HomeSliderInterface
     */
    public function setName($name);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set website_id
     * @param int $websiteId
     * @return \MMT\HomeSlider\HomeSlider\Api\Data\HomeSliderInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get website_id
     * @return int|null
     */
    public function getWebsiteId();


    /**
     * set home slider image
     * @param string $image
     * @return $this
     */
    public function setHomeSliderImage($image);

    /**
     * get home slider image
     * @return string
     */
    public function getHomeSliderImage();

    /**
     * set home slider image mobile
     * @param string $image
     * @return $this
     */
    public function setHomeSliderImageMobile($image);

    /**
     * get home slider image mobile
     * @return string
     */
    public function getHomeSliderImageMobile();

     /**
     * set home slider image mobile app
     * @param string $image
     * @return $this
     */
    public function setHomeSliderImageMobileApp($image);

    /**
     * get home slider image mobile
     * @return string
     */
    public function getHomeSliderImageMobileApp();

    /**
     * Set category_id
     * @param int $categoryId
     * @return \MMT\HomeSlider\HomeSlider\Api\Data\HomeSliderInterface
     */
    public function setCategoryId($categoryId);

    /**
     * Get categoryId
     * @return int|null
     */
    public function getCategoryId();

    /**
     * Returns rule isActive
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive);



    /**
     * set is home slider
     * @param int $isHomeSlider
     * @return $this
     */
    public function setIsHomeSlider($isHomeSlider);

    /**
     * get is home slider
     * @return int
     */
    public function getIsHomeSlider();

    /**
     * set is home slider one
     * @param int $isHomeSliderOne
     * @return $this
     */
    public function setIsHomeSliderOne($isHomeSliderOne);

    /**
     * get is home slider one
     * @return int
     */
    public function getIsHomeSliderOne();

    /**
     * set percentage name
     * @param string $percentageName
     * @retrun $this
     */
    public function setPercentageName($percentageName);

    /**
     * get percentage name
     * @return string
     */
    public function getPercentageName();

    /**
     * Set conditions_serialized
     * @param string $conditionsSerialized
     * @return $this
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Get conditions_serialized
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * set banner image
     * @param string $banner_image
     * @return $this
     */
    public function setBannerImage(string $banner_image);

    /**
     * get banner image
     * @return string
     */
    public function getBannerImage();
}