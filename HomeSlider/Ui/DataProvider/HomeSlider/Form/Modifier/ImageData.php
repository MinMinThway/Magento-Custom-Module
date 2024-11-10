<?php

namespace MMT\HomeSlider\Ui\DataProvider\HomeSlider\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use MMT\HomeSlider\Model\ResourceModel\HomeSlider\CollectionFactory;

class ImageData implements ModifierInterface
{
    /**
     * @var \Other\Rule\Model\ResourceModel\HomeSlider\Collection
     */
    protected $collection;

    /**
     * @param CollectionFactory $imageCollectionFactory
     */
    public function __construct(
        CollectionFactory $imageCollectionFactory
    ) {
        $this->collection = $imageCollectionFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        /** @var $image \MMT\HomeSlider\Model\HomeSlider */
        foreach ($items as $image) {
            $_data = $image->getData();
            if (isset($_data['home_slider_image'])) {
                $imageArr = [];
                $imageArr[0]['name'] = 'home_slider_image';
                $imageArr[0]['url'] = $image->getHomeSliderImageUrl();
                $_data['home_slider_image'] = $imageArr;
            }
            if (isset($_data['home_slider_image_mobile'])) {
                $imageArr = [];
                $imageArr[0]['name'] = 'home_slider_image_mobile';
                $imageArr[0]['url'] = $image->getHomeSliderImageMobileUrl();
                $_data['home_slider_image_mobile'] = $imageArr;
            }
            if (isset($_data['home_slider_image_mobile_app'])) {
                $imageArr = [];
                $imageArr[0]['name'] = 'home_slider_image_mobile_app';
                $imageArr[0]['url'] = $image->getHomeSliderImageMobilAppeUrl();
                $_data['home_slider_image_mobile_app'] = $imageArr;
            }
            if (isset($_data['banner_image'])) {
                $imageArr = [];
                $imageArr[0]['name'] = 'banner_image';
                $imageArr[0]['url'] = $image->getBannerImageUrl();
                $_data['banner_image'] = $imageArr;
            }
            $image->setData($_data);
            $data[$image->getHomesliderId()] = $_data;
        }
        return $data;
    }
}