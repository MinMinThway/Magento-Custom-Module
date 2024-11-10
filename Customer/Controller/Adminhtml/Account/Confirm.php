<?php

declare(strict_types=1);

namespace MMT\Customer\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Backend\Model\View\Result\Redirect;

class Confirm extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerRepository $customerRepository
    ) {
        $this->filter = $filter;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $count = 0;

        $collection = $this->filter->getCollection($this->customerCollectionFactory->create()->addFieldToSelect('email'));
        foreach ($collection as $item) {
            $customer = $this->customerRepository->get($item->getEmail());
            if ($customer->getConfirmation()) {
                $customer->setConfirmation(null);
                $this->customerRepository->save($customer);
                $count++;
            }
        }

        $this->messageManager->addSuccessMessage(__('Confirmation account was successful for ' . $count . ' accounts.'));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}