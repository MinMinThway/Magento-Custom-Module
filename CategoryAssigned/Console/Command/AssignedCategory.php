<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MMT\CategoryAssigned\Console\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\CategoryFactory;

class AssignedCategory extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";


    /**
     * @var ProductCategoryList
     */
    public $productCategory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /** @var \Magento\Framework\App\State **/
    private $state;

    public function __construct(
        ProductCategoryList $productCategory,
        CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagementInterface,
        \Magento\Framework\App\State $state,
        string $name = null
    ) {
        $this->productCategory = $productCategory;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryLinkManagement = $categoryLinkManagementInterface;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);

        foreach ($this->getProductCollection() as $product) {
            $categoryIds = $this->getCategoryIdByProduct(intval($product->getId()));
            $updateCategoryList = [];
            $updateCategoryList = $categoryIds;
            $parentCategories = [];
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryFactory->create()->load($categoryId);
                $parentCategories = $category->getParentCategories();
                foreach ($parentCategories as $parentCategory) {
                    $updateCategoryList[] = $parentCategory->getId();
                }
            }
            try {
                $this->categoryLinkManagement->assignProductToCategories(
                    $product->getSku(),
                    array_unique($updateCategoryList)
                );
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln("Assigned Success");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("mit_categoryassigned:assignedcategory");
        $this->setDescription("To assigned category");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }


    /**
     * get all the category id
     *
     * @param int $productId
     * @return array
     */
    public function getCategoryIdByProduct(int $productId)
    {
        $categoryIds = $this->productCategory->getCategoryIds($productId);
        $category = [];
        if ($categoryIds) {
            $category = array_unique($categoryIds);
        }
        return $category;
    }


    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['entity_id', 'sku']);
        return $collection;
    }
}
