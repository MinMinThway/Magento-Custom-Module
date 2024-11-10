<?php

namespace MMT\Product\Api;

interface CustomLayerNavigationInterface
{

    /**
     * get layer navigation by category id
     * 
     * @param int $categoryId
     * @return array
     */
    public function getLayerNavigationByCategoryId($categoryId);

    /**
     * get layer navigation by category id and active filters
     * 
     * @param int $categoryId
     * @param \MMT\Product\Api\Data\CustomKeyValInterface[] $items
     * @return array
     */
    public function getLayerNavigationByCategoryIdAndActivFilters($categoryId, array $items);
}
