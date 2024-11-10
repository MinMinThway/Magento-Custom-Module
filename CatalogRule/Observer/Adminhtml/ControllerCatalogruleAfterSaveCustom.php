<?php

namespace MMT\CatalogRule\Observer\Adminhtml;

use MMT\CatalogRule\Helper\CustomDataUpdater;

class ControllerCatalogruleAfterSaveCustom implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CustomDataUpdater
     */
    private $customDataUpdater;

    public function __construct(
        CustomDataUpdater $customDataUpdater
    ) {
        $this->customDataUpdater = $customDataUpdater;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        //Your observer code
        $id = $observer->getData('id');
        if ($id) {
            // $this->customDataUpdater->updateData($id, $observer->getData('type'));
        }
        return $this;
    }
}
