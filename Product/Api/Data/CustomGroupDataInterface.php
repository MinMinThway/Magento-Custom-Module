<?php

namespace MMT\Product\Api\Data;

use Magento\Store\Api\Data\GroupInterface;

interface CustomGroupDataInterface extends GroupInterface {
    const STORES = 'stores';

    /**
     * get stores
     * @return \MMT\Product\Api\Data\CustomStoreDataInterface[]
     */
    public function getStores();

    /**
     * set stores
     * @param \MMT\Product\Api\Data\CustomStoreDataInterface[] $stores
     * @return $this
     */
    public function setStores($stores);
}