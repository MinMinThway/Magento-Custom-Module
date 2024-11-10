<?php

namespace MMT\ProductSorter\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar as ProductListToolbar;

class Toolbar extends ProductListToolbar
{
    /**
     * @var string
     */
    protected $_template = 'MMT_ProductSorter::product/list/toolbar.phtml';

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $sortArr = explode('_', $this->getCurrentOrder());

            if (count($sortArr) == 2) {
                if ($sortArr[0] == 'position') {
                    $this->_collection->addAttributeToSort(
                        $sortArr[0],
                        $sortArr[1]
                    );
                } else {
                    $this->_collection->setOrder($sortArr[0], $sortArr[1]);
                }
            } else {
                if ($sortArr[0] == 'position') {
                    $this->_collection->addAttributeToSort(
                        $sortArr[0],
                        $this->getCurrentDirection()
                    );
                } else {
                    $this->_collection->setOrder($sortArr[0], $this->getCurrentDirection());
                }
            }
        }
        return $this;
    }
}

