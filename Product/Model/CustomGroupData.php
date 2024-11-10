<?php

namespace MMT\Product\Model;

use Magento\Store\Model\Group;
use MMT\Product\Api\Data\CustomGroupDataInterface;

class CustomGroupData extends Group implements CustomGroupDataInterface
{

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        return $this->getData(self::STORES);
    }

    /**
     * @inheritdoc
     */
    public function setStores($stores)
    {
        return $this->setData(self::STORES, $stores);
    }
}
