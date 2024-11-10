<?php

namespace MMT\Product\Api;

interface CustomProductInterface
{

    /**
     * get product by id including review/rating
     * @param int $id
     * @return \MMT\Product\Api\Data\CustomProductManagementInterface
     */
    public function getProductDetailBySku($id);

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param bool $customSort
     * @param string $sortQuery
     * @return \MMT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '');
}

