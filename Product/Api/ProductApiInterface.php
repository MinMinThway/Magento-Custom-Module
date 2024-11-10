<?php

namespace MMT\Product\Api;

interface ProductApiInterface
{

    /**
     * get product list
     * 
     * @param int $categoryId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param bool $customSort
     * @param string $sortQuery
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getList($categoryId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '');
}

