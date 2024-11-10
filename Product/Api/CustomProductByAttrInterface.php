<?php

namespace MMT\Product\Api;

interface CustomProductByAttrInterface {

     /**
     * Get product list
     *
     * @param int $type
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function getAttrProductList($type, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}
