<?php

namespace MMT\Product\Api\Data;

use Magento\Store\Api\Data\WebsiteInterface;

interface CustomWebsiteDataInterface extends WebsiteInterface {
    const URL = 'url';
    const GROUP = 'group';
    const IMAGE = 'image';

    /**
     * get url
     * @param string
     */
    public function getUrl();

    /**
     * set url
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * get group
     * @return \MMT\Product\Api\Data\CustomGroupDataInterface
     */
    public function getGroup();

    /**
     * set group
     * @param \MMT\Product\Api\Data\CustomGroupDataInterface $group
     * @return $this
     */
    public function setGroup($group);

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

}