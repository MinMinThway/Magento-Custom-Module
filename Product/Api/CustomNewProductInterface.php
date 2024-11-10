<?php

namespace MMT\Product\Api;

interface CustomNewProductInterface {

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getNewProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);


    /**
     * Get product list all
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get bundle product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getBundleProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get bundle product list for page
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getBundleProductListForPage(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}
