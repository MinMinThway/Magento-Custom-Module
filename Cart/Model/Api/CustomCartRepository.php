<?php

namespace MMT\Cart\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\GuestCart\GuestCartItemRepository;
use Magento\Quote\Model\Quote\Item\Repository;
use MMT\Cart\Api\CustomCartItemRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class CustomCartRepository implements CustomCartItemRepositoryInterface
{

    /**
     * @var \Magento\Quote\Model\Quote\Item\Repository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var \Magento\Quote\Model\GuestCart\GuestCartItemRepository
     */
    private $guestCartItemRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;

    public function __construct(
        Repository $repository,
        ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Configurable $configurable,
        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory,
        GuestCartItemRepository $guestCartItemRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->quoteRepository = $repository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->configurable = $configurable;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->guestCartItemRepository = $guestCartItemRepository;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->resourceProduct = $resourceProduct;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritDoc
     */
    public function getList($cartId)
    {
        $result = $this->quoteRepository->getList($cartId);
        if (is_array($result)) {
            /** @var  \MMT\Cart\Model\Quote\CustomItem  $item */
            foreach ($result as $item) {
                $item->setSubTotal($item->getRowTotalInclTax());
                $this->getImgFullPath($item);
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getListForGuest($cartId)
    {
        $result = $this->guestCartItemRepository->getList($cartId);
        if (is_array($result)) {
            /** @var  \MMT\Cart\Model\Quote\CustomItem  $item */
            foreach ($result as $item) {
                $item->setSubTotal($item->getRowTotalInclTax());
                $this->getImgFullPath($item);
            }
        }
        return $result;
    }

    /**
     * @param \MMT\Cart\Model\Quote\CustomItem $item
     */
    private function getImgFullPath($item)
    {
        $currentStore = $this->storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        if ($item->getProductType() == 'bundle') {
            $product = $this->productRepository->getById($item->getProduct()->getId());
            $item->setImgPath($baseUrl . 'media/catalog/product' .  $product->getData('image'));
        } else {
            $product = $this->productRepository->get($item->getSku());
            $item->setParentId($product->getId());
            $parentIdsConfig = $this->configurable->getParentIdsByChild($product->getId());
            if (is_array($parentIdsConfig) && count($parentIdsConfig) > 0) {
                $parentProduct = $this->productRepository->getById($parentIdsConfig[0]);
                $productAttributeOptions = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);
                $attributeOptions = array();
                foreach ($productAttributeOptions as $productAttribute) {
                    foreach ($productAttribute['values'] as $attribute) {
                        if ($product->getCustomAttributes()) {
                            foreach ($product->getCustomAttributes() as $customAttribute) {
                                if ($customAttribute->getValue() == $attribute['value_index'] && $customAttribute->getAttributeCode() == $productAttribute['attribute_code']) {
                                    $attributeOptions[] = $this->attributeValueFactory->create()->setAttributeCode($productAttribute['label'])->setValue($attribute['store_label']);
                                }
                            }
                        }
                    }
                }
                $item->setName($parentProduct->getName());
                $item->setAttributes($attributeOptions);
                $item->setParentId($parentProduct->getId());
            }
            $item->setImgPath($baseUrl . 'media/catalog/product' .  $product->getData('image'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateItemByItemId(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $cartId = $cartItem->getQuoteId();
        if (!$cartId) {
            throw new InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'quoteId'])
            );
        }

        $quote = $this->cartRepositoryInterface->getActive($cartId);
        $quoteItems = $quote->getItems();
        $quoteItems[] = $cartItem;
        $quote->setItems($quoteItems);
        $this->cartRepositoryInterface->save($quote);
        $quote->collectTotals();
        return $quote->getLastAddedItem();
    }

    /**
     * {@inheritdoc}
     */
    public function updateGuestItemByItemId(\Magento\Quote\Api\Data\CartItemInterface $cartItem)
    {
        return $this->guestCartItemRepository->save($cartItem);
    }


    /**
     * Get Super attribute details by the child id
     * @param int $childId
     * @return array
     */
    public function getChildSuperAttribute($childId)
    {
        $parentIds = $this->resourceConfigurable->getParentIdsByChild($childId);
        if (!empty($parentIds)) {
            $skus = $this->resourceProduct->getProductsSku($parentIds);
        }
        $parentProduct = $this->getProduct($skus[0]['entity_id']);
        $childProduct = $this->getProduct($childId);
        $_attributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributes($parentProduct);

        $attributesPair = [];
        foreach ($_attributes as $_attribute) {
            $attributeId = (int) $_attribute->getAttributeId();
            $attributeCode = $this->getAttributeCode($attributeId);
            $attributesPair[$attributeId] = (int) $childProduct->getData($attributeCode);
        }
        return $attributesPair;
    }

    /**
     * Get attribute code by attribute id
     * @param int $id
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAttributeCode(int $id)
    {
        return $this->attributeRepository->get($id)->getAttributeCode();
    }

    /**
     * Get Product Object by id
     * @param int $id
     * @return ProductInterface|null
     */
    public function getProduct(int $id)
    {
        $product = null;
        try {
            $product = $this->productRepository->getById($id);
        } catch (NoSuchEntityException $exception) {
        }
        return $product;
    }
}
