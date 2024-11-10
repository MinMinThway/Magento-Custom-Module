<?php

namespace MMT\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use MMT\Product\Model\SimpleProductFactory;

class SimpleProductHelper extends AbstractHelper
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var SimpleProductFactory
     */
    private $simpleProductFactory;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezoneInterface,
        SimpleProductFactory $simpleProductFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->timezoneInterface = $timezoneInterface;
        $this->simpleProductFactory = $simpleProductFactory;
    }

    public function getChildProductList($childIds,$configAttributeCode)
    {
        if (count($childIds) > 0) {
            $collection = $this->productCollectionFactory->create();
            $scopeTz = new \DateTimeZone(
                $this->timezoneInterface->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
            );
            $date = (new \DateTime('now', $scopeTz))->getTimestamp();
            $collection->addAttributeToSelect('*');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinField('stock_qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
            $collection->getSelect()->order('FIELD(e.entity_id,' . implode(',', $childIds) . ')');

            $collection->addFieldToFilter('entity_id', ['in' => $childIds]);

            $collection->addMediaGalleryData();

            //echo($collection->getSelect()->__toString());
            $result = [];

            $pointReview = 0;
            $freeImgPath = '';

            $index = 0;
            foreach ($collection as $data) {

                $item = $this->simpleProductFactory->create();
                $check =  false;
                $reward = 0;
                $regularPrice = $data->getPriceInfo()->getPrice('regular_price')->getAmount();
                $finalPrice   = $data->getPriceInfo()->getPrice('final_price')->getAmount();

                $discountLabel        = '';

                // if ($data->getTypeId() === 'simple') {

                $regular = 0;
                $special = 0;
                $regular = $regularPrice->getValue() ? $regularPrice->getValue() : $collection->getData()[$index]['price'];
                $special = $finalPrice->getValue() ? $finalPrice->getValue() : $collection->getData()[$index]['final_price'];
                $regular = $regularPrice->getValue();
                $special = $finalPrice->getValue();

                $item->setId($data->getId());
                $item->setName($data->getName());
                $item->setSku($data->getSku());
                $item->setPrice($regular);
                $item->setDiscountPrice($special);
                $item->setImage($this->getCustomImgUrl($data));
                $item->setDiscountLabel($discountLabel);
                $item->setMpRewardPoints($reward);
                $item->setMpReviewPoints($pointReview);
                $item->setIsFreeShipping($check);
                $item->setFreeShippingImg('');
                $item->setStockQty(isset($collection->getData()[$index]['stock_qty']) ? $collection->getData()[$index]['stock_qty'] : 0);
                if ($data->getStatus() > 1) {
                    $item->setStockQty(0);
                }
                $item->setAttributes($data->getCustomAttributes());
                $itemGetAttribute = $item->getAttributes(); 
                       
                if ($check) {
                    $item->setFreeShippingImg($freeImgPath);
                }

                $configAttributeSet = []; 
                foreach($itemGetAttribute as $itemGetAttributeList){
                    $attribute_code = $itemGetAttributeList->getAttributeCode();
                    $attribute_value = $itemGetAttributeList->getValue();
                    if (isset($configAttributeCode) && is_array($configAttributeCode) && count($configAttributeCode) > 0) {
                        if (in_array($attribute_code, $configAttributeCode)) {
                            $configAttributeSet[] = [
                                'attribute_code' => $attribute_code,
                                'value' => $attribute_value                          
                            ];
                        }                        
                    }                    
                }
                $item->setConfigurableAttributes($configAttributeSet);

                $result[] = $item;
                $index++;
            }
            return $result;
        } else {
            return [];
        }
    }

    private function getCustomImgUrl($product)
    {
        $image_url = $this->imageHelper->init($product, 'product_page_image_small')
            ->setImageFile($product->getSmallImage())->resize(512)->getUrl();
        return $image_url;
    }
}

