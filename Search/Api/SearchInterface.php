<?php

namespace MMT\Search\Api;

interface SearchInterface
{
    /**
     * make full text search and return product list
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \MMT\Product\Api\Data\ProductResultListInterface
     */
    public function search(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria);

    /**
     * search auto suggestion api
     * @param string $q
     * @return array
     */
    public function searchSuggestion($q); 
}
