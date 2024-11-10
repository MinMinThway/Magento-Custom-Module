<?php

namespace MMT\Product\Model;

use Magento\Store\Model\Website;
use MMT\Product\Api\Data\CustomWebsiteDataInterface;

class CustomWebsiteData extends Website implements CustomWebsiteDataInterface
{

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @inheritdoc
     */
    public function getGroup()
    {
        return $this->getData(self::GROUP);
    }

    /**
     * @inheritdoc
     */
    public function setGroup($group)
    {
        return $this->setData(self::GROUP, $group);
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
}
