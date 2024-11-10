<?php

namespace MMT\Search\Model\Api;

use MMT\Search\Api\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use MMT\Product\Api\ProductApiInterface;
use MMT\Product\Api\Data\ProductResultListInterfaceFactory;

class Search implements SearchInterface
{
    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    private $searchResponseBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductApiInterface
     */
    private $productApiInterface;

    /**
     * @var ProductResultListInterfaceFactory
     */
    private $productResultListInterfaceFactory;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param Builder $requestBuilder
     * @param ScopeResolverInterface $scopeResolver
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     * @param ProductApiInterface $productApiInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductResultListInterfaceFactory $productResultListInterfaceFactory
     * @param SearchHelper $searchHelper
     * @param Curl $curl
     * @param RequestInterface $requestInterface
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Builder $requestBuilder,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder,
        ProductApiInterface $productApiInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        ProductResultListInterfaceFactory $productResultListInterfaceFactory,
        SearchHelper $searchHelper,
        Curl $curl,
        RequestInterface $requestInterface,
        UrlInterface $urlInterface
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->scopeResolver = $scopeResolver;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->productApiInterface = $productApiInterface;
        $this->productResultListInterfaceFactory = $productResultListInterfaceFactory;
        $this->searchHelper = $searchHelper;
        $this->curl = $curl;
        $this->requestInterface = $requestInterface;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @inheritdoc
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $this->requestBuilder->setRequestName($searchCriteria->getRequestName());

        $scope = $this->scopeResolver->getScope()->getId();
        $this->requestBuilder->bindDimension('scope', $scope);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->addFieldToFilter($filter->getField(), $filter->getValue());
            }
        }

        // if ($searchCriteria->getCurrentPage() > 1) {
        //     $this->requestBuilder->setFrom(($searchCriteria->getCurrentPage() - 1) * $searchCriteria->getPageSize());
        // }
        // $this->requestBuilder->setSize(100); //$searchCriteria->getPageSize());

        /**
         * This added in Backward compatibility purposes.
         * Temporary solution for an existing API of a fulltext search request builder.
         * It must be moved to different API.
         * Scope to split Search request builder API in MC-16461.
         */
        if (method_exists($this->requestBuilder, 'setSort')) {
            $this->requestBuilder->setSort($searchCriteria->getSortOrders());
        }
        $request = $this->requestBuilder->create();
        $searchResponse = $this->searchEngine->search($request);

        $searchResultInterface = $this->searchResponseBuilder->build($searchResponse);
        $productIdArr = [];
        $productArr = [];
        $productItemList = [];
        $productSearchResultInterface = $this->productResultListInterfaceFactory->create();

        foreach ($searchResultInterface->getItems() as $document) {
            $productId = $document->getId();
            $productIdArr[] = $productId;
        }

        if (count($productIdArr) > 0) {
            $filteredId = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('entity_id')
                ->setValue($productIdArr)
                ->create();

            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $searchCriteriaData = $this->searchCriteriaBuilder->setFilterGroups($filterGroupList)->create();
            $searchCriteriaData->setPageSize($searchCriteria->getPageSize());
            $searchCriteriaData->setCurrentPage($searchCriteria->getCurrentPage());
            $getSearchCriteriaType = $searchCriteriaData->getSortOrders();

            if (isset($getSearchCriteriaType)) {
                $productSearchResultInterface = $this->productApiInterface->getList(0, $searchCriteriaData);
            } else {
                $productSearchResultInterface = $this->productApiInterface->getList(0, $searchCriteriaData, true, 'FIELD(e.entity_id,' . implode(',', $productIdArr) . ')');
            }

            $productArr = $productSearchResultInterface->getItems();

            foreach ($productArr as $product) {
                if (in_array($product->getId(), $productIdArr)) {
                    $productItemList[] = $product;
                }
            }
        }
        $productSearchResultInterface->setSearchCriteria($searchCriteria);
        //$productSearchResultInterface->setTotalCount($productSearchResultInterface->getTotalCount());
        $productSearchResultInterface->setItems($productItemList);
        return $productSearchResultInterface;
    }

    /**
     * @inheritdoc
     */
    public function searchSuggestion($q)
    {
        if (isset($q) && strlen($q) >= $this->searchHelper->getMinQueryLength()) {
            $url = $this->urlInterface->getUrl(
                'search/ajax/suggest',
                ['_query' => [QueryFactory::QUERY_VAR_NAME => $q], '_secure' => $this->requestInterface->isSecure()]
            );

            $this->curl->get($url);
            $result = $this->curl->getBody();
            $responseData = json_decode($result, true);
            return $responseData;
        }
        return [];
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param string|array|null $condition
     * @return $this
     */
    private function addFieldToFilter($field, $condition = null)
    {
        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'], true)) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }
}
