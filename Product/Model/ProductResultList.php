<?php

namespace MMT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Product\Api\Data\ProductResultListInterface;

class ProductResultList extends AbstractExtensibleModel implements ProductResultListInterface {

        /**
     * get Items
     * @return \MMT\Product\Api\Data\ProductInterface[]
     */
    public function getItems() {
        return $this->getData(self::ITEMS);
    }

    /**
     * set Items
     *
     * @param \MMT\Product\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items) {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * get page size
     * @return int
     */
    public function getPageSize() {
        return $this->getData(self::PAGE_SIZE);
    }

    /**
     * set page size
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize) {
        return $this->setData(self::PAGE_SIZE, $pageSize);
    }

    /**
     * get current page
     * @return int
     */
    public function getCurrentPage() {
        return $this->getData(self::CURRENT_PAGE);
    }

    /**
     * set current page
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage) {
        return $this->setData(self::CURRENT_PAGE, $currentPage);
    }

    /**
     * get total count
     * @return int
     */
    public function getTotalCount() {
        return $this->getData(self::TOTAL_COUNT);
    }

    /**
     * set total count
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount) {
        return $this->setData(self::TOTAL_COUNT, $totalCount);
    }
}