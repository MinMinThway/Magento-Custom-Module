<?php

namespace MMT\Product\Api\Data;

interface ProductResultListInterface {
    const ITEMS = 'items';
    const PAGE_SIZE = 'page_size';
    const CURRENT_PAGE = 'current_page';
    const TOTAL_COUNT = 'total_count';

    /**
     * get Items
     * @return \MMT\Product\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * set Items
     *
     * @param \MMT\Product\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * get page size
     * @return int
     */
    public function getPageSize();

    /**
     * set page size
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * get current page
     * @return int
     */
    public function getCurrentPage();

    /**
     * set current page
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage);

    /**
     * get total count
     * @return int
     */
    public function getTotalCount();

    /**
     * set total count
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount);
}