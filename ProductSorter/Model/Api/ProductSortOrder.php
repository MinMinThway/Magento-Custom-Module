<?php

namespace MMT\ProductSorter\Model\Api;

use Magento\Store\Model\StoreManagerInterface;
use MMT\ProductSorter\Api\ProductSortOrderInterface;

class ProductSortOrder implements ProductSortOrderInterface
{

    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        \Magento\Catalog\Model\Config $catalogConfig,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @inheritdoc
     */
    public function getProductSortOrder()
    {
        $result = [];
        $sortOrder = $this->catalogConfig->getAttributeUsedForSortByArray();
        foreach ($sortOrder as $key => $value) {
            if ($key !== 'mpmembership_price_fixed') {
                if ($key == 'price_asc') {
                    $data['key'] = 'price';
                    $data['order'] = self::SORT_ORDER_ASC;
                    $data['mm_value'] = 'အနည်းဆုံးစျေးမှစ၍';
                } else if ($key == 'price_desc') {
                    $data['key'] = 'price';
                    $data['order'] = self::SORT_ORDER_DESC;
                    $data['mm_value'] = 'အများဆုံးစျေးမှစ၍';
                } else {
                    $data['key'] = $key;
                    $data['order'] = self::SORT_ORDER_DESC;
                    switch ($data['key']) {
                        case 'position':
                            $data['mm_value'] = 'ကိုက်ညီမှုအရှိဆုံး';
                            break;
                        case 'best_seller':
                            $data['mm_value'] = 'ရောင်းအားအကောင်းဆုံး';
                            break;
                        case 'created_at':
                            $data['mm_value'] = 'အသစ်ရောက်ရှိသောပစ္စည်းများ';
                            break;
                    }
                }
                $data['value'] = $value;
                $data['is_default'] = $this->catalogConfig->getProductListDefaultSortBy($this->storeManagerInterface->getStore()->getId()) == $key;
                $result[] = $data;
            }
        }
        return $result;
    }
}

