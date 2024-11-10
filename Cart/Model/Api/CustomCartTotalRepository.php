<?php

namespace MMT\Cart\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use MMT\Cart\Api\Data\CustomTotalsInterfaceFactory;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\ShippingAddressManagement;
use MMT\Cart\Api\CustomCartTotalRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\Quote\Item\Repository as QuoteItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ResourceConnection;

class CustomCartTotalRepository implements CustomCartTotalRepositoryInterface
{
    /**
     * Cart totals factory.
     *
     * @var CustomTotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * Cart totals factory.
     *
     * @var TotalsInterfaceFactory
     */
    private $totalsInterfaceFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CouponManagementInterface
     */
    protected $couponService;

    /**
     * @var TotalsConverter
     */
    protected $totalsConverter;

    /**
     * @var ShippingAddressManagement
     */
    private $shippingAddressManagement;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteItemRepository
     */
    private $quoteItemRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var Product
     */
    private $resourceProduct;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param CustomTotalsInterfaceFactory $totalsFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CouponManagementInterface $couponService
     * @param TotalsConverter $totalsConverter
     * @param TotalsInterfaceFactory $totalsInterfaceFactory
     * @param ShippingAddressManagement  $shippingAddressManagement
     * @param ProductRepository $productRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteItemRepository $quoteItemRepository
     * @param Configurable $configurable
     * @param AttributeValueFactory $attributeValueFactory
     * @param RuleFactory $ruleFactory
     * @param TimezoneInterface $timezoneInterface
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        CustomTotalsInterfaceFactory $totalsFactory,
        CartRepositoryInterface $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        CouponManagementInterface $couponService,
        TotalsConverter $totalsConverter,
        TotalsInterfaceFactory $totalsInterfaceFactory,
        ShippingAddressManagement  $shippingAddressManagement,
        ProductRepository $productRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteItemRepository $quoteItemRepository,
        Configurable $configurable,
        RuleFactory $ruleFactory,
        TimezoneInterface $timezoneInterface,
        ImageHelper $imageHelper,
        Product $resourceProduct,
        ResourceConnection $resourceConnection
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
        $this->totalsInterfaceFactory = $totalsInterfaceFactory;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->productRepository = $productRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->configurable = $configurable;
        $this->ruleFactory = $ruleFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->imageHelper = $imageHelper;
        $this->resourceProduct = $resourceProduct;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->collectTotals();
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }

        unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        /** @var TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            TotalsInterface::class
        );


        $result = $this->quoteItemRepository->getList($cartId);
        if (is_array($result)) {
            /** @var  \MMT\Cart\Model\Quote\CustomItem  $item */
            foreach ($result as $item) {
                $item->setSubTotal($item->getRowTotalInclTax());
                $this->getImgFullPath($item);
            }
        }

        $calculatedTotals = $this->totalsConverter->process($addressTotals);

        $customQuoteTotals = $this->totalsFactory->create();

        $quoteTotals->setTotalSegments($calculatedTotals);

        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());
        $customQuoteTotals = $quoteTotals;
        if (!$quote->isVirtual()) {
            $address = $this->shippingAddressManagement->get($cartId);
            $customQuoteTotals->setShippingAddress($address);
        }
        $customQuoteTotals->setItems($result);

        $customQuoteTotals->setShowGiftProduct(false);
        $giftMaxQty = $this->calculateGiftMaxQty($quote);
        if ($giftMaxQty) {
            $customQuoteTotals->setShowGiftProduct(true);
        }
        $customQuoteTotals->setCartId($cartId);

        return $customQuoteTotals;
    }

    /**
     * @inheritdoc
     */
    public function getTotalForGuest($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->get($quoteIdMask->getQuoteId());
    }

    /**
     * @inheritdoc
     */
    public function getCartTotalItemsCount($cartId)
    {
        try {
            $quote = $this->quoteRepository->get($cartId);
            return intval($quote->getItemsQty());
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCartTotalItemsCountGuest($cartId)
    {
        try {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quote = $this->quoteRepository->get($quoteIdMask->getQuoteId());
            return intval($quote->getItemsQty());
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * add full image url
     * @param \MMT\Cart\Model\Quote\CustomItem $item
     */
    public function getImgFullPath($item)
    {
        $item->setProductId($item->getProduct()->getId());
        if ($item->getProductType() == 'bundle') {
            $product = $this->productRepository->getById($item->getProduct()->getId());
            // $item->setImgPath($baseUrl . 'media/catalog/product' .  $product->getData('image'));
            $item->setImgPath($this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($product->getSmallImage())->resize(194)->getUrl());
        } else {
            $connection = $this->resourceConnection->getConnection();
            $eavTbl = $connection->getTableName('eav_attribute');
            $eavAttrOptTbl = $connection->getTableName('eav_attribute_option');
            $eavAttrOptValTbl = $connection->getTableName('eav_attribute_option_value');
            $childId = $this->resourceProduct->getIdBySku($item->getSku());
            $result = [];
            if ($item->getProductType() === 'configurable') {
                $parentIdsConfig = $this->configurable->getParentIdsByChild($childId);
                foreach ($item->getProductOption()->getExtensionAttributes()->getConfigurableItemOptions() as $config) {
                    $select = $connection->select()->from(['eaov' => $eavAttrOptValTbl], 'eaov.value')
                        ->joinInner(['eao' => $eavAttrOptTbl], 'eaov.option_id = eao.option_id', [])
                        ->joinInner(['eav' => $eavTbl], 'eav.attribute_id = eao.attribute_id', ['eav.frontend_label'])
                        ->where('eav.attribute_id = ?', $config->getOptionId())
                        ->where('eaov.option_id = ?', $config->getOptionValue());
                    $row = $connection->fetchRow($select);
                    $result[] = array($row['frontend_label'] => $row['value']);
                }
                $item->setParentId($parentIdsConfig[0]);
            }
            $item->setAttributes($result);

            $catalogProductEntityIntTable = $connection->getTableName('catalog_product_entity_int');
            $catalogProductEntityVarcharTable = $connection->getTableName('catalog_product_entity_varchar');
            $eavAttributeTable = $connection->getTableName('eav_attribute');
            $attributeCode = 'image';
            $subquery = $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', $attributeCode);
            $selectImage = $connection->select()
                ->from(
                    ['cpev' => $catalogProductEntityVarcharTable],
                    ['image_file_path' => 'cpev.value']
                )
                ->join(
                    ['cpei' => $catalogProductEntityIntTable],
                    'cpei.entity_id = cpev.entity_id',
                    []
                )
                ->where('cpei.entity_id = ?', $childId)
                ->where('cpev.attribute_id IN (?)', new \Zend_Db_Expr($subquery))
                ->limit(1);
            $imageFilePath = $connection->fetchOne($selectImage);

            $item->setImgPath($this->imageHelper->init(null, 'product_page_image_small')
                ->setImageFile($imageFilePath)
                ->resize(194)
                ->getUrl());
        }
    }

    /**
     * get sales rule ids
     * @param \Magento\Quote\Model\Quote $quoteObj
     * @return array
     */
    public function getAppliedRuleIdsSalesRule($quoteObj)
    {
        $ids = [];
        $collection = $this->ruleFactory->create()->getCollection();
        $collection->addFieldToSelect(['rule_id', 'from_date', 'to_date']);
        $collection->addFieldToFilter('simple_action', ['eq' => 'offer_product'])
            ->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->getSelect()
            ->where('main_table.from_date is null or main_table.from_date <= ? ', $this->timezoneInterface->date()->format('Y-m-d'))
            ->where('main_table.to_date is null or main_table.to_date >= ? ', $this->timezoneInterface->date()->format('Y-m-d'));

        foreach ($collection as $rule) {
            $validate = $rule->getConditions()->validate($quoteObj);
            if ($validate) {
                $ids[] = $rule->getRuleId();
            }
        }
        return $ids;
    }

    /**
     * calculate gift max qty
     * @param \Magento\Quote\Model\Quote $quoteObj
     * @return int
     */
    public function calculateGiftMaxQty($quoteObj)
    {
        $collection = $this->ruleFactory->create()->getCollection();
        $collection->addFieldToFilter('simple_action', ['eq' => 'offer_product'])
            ->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->getSelect()
            ->where('main_table.from_date is null or main_table.from_date <= ? ', $this->timezoneInterface->date()->format('Y-m-d'))
            ->where('main_table.to_date is null or main_table.to_date >= ? ', $this->timezoneInterface->date()->format('Y-m-d'));

        $allGiftMaxQty = 0;
        foreach ($collection as $rule) {
            $validate = $rule->getConditions()->validate($quoteObj);
            if ($validate) {
                $ruleId = $rule->getRuleId();

                /** @var \Magento\SalesRule\Model\Rule $rule */
                $rule = $this->ruleFactory->create()->load($ruleId);
                $ruleData = $rule->getConditionsSerialized();
                $ruleDataArray = json_decode($ruleData, true);
                if (isset($ruleDataArray['conditions'])) {
                    $conditions = $ruleDataArray['conditions'];
                    foreach ($conditions as $condition) {
                        if (isset($condition['conditions'])) {
                            $productConditions = $condition['conditions'];
                            foreach ($productConditions as $productCondition) {
                                if (isset($productCondition['value'])) {
                                    $skuValues = $productCondition['value'];
                                    $skuValues = explode(",", $skuValues);
                                    $skus = [];
                                    foreach ($skuValues as $skuValue) {
                                        $product = $this->productRepository->get($skuValue);
                                        $productType = $product->getTypeId();

                                        if ($productType == "configurable") {
                                            $_children = $product->getTypeInstance()->getUsedProducts($product);
                                            foreach ($_children as $child) {
                                                $skus[] = $child->getSku();
                                            }
                                        }
                                        $skus[] = $skuValue;
                                    }
                                }
                            }
                        }
                    }
                }
                $itemQty = [];
                foreach ($quoteObj->getAllVisibleItems() as $item) {
                    if (in_array($item->getSku(), $skus)) {
                        $itemQty[] = $item->getQty();
                    }
                }
                $itemQtySum = array_sum($itemQty);
                $giftMaxQty = (intval($itemQtySum / $rule->getDiscountStep())) * $rule->getDiscountAmount();
                $allGiftMaxQty += $giftMaxQty;
            }
        }
        return $allGiftMaxQty;
    }
}
