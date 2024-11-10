<?php

namespace MMT\Product\Api;

interface CustomBestSellerInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getBestSellerProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}

