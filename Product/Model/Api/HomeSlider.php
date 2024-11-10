<?php

namespace MMT\Product\Model\Api;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use MMT\CatalogRule\Helper\HomeSliderGenerator;
use MMT\Product\Api\HomeSliderInterface;
use MMT\HomeSlider\Model\ResourceModel\HomeSlider\CollectionFactory as HomeSliderCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;

class HomeSlider implements HomeSliderInterface
{
    /**
     * cms block identifier for home slider
     */
    const HOME_SLIDER_BLOCK_IDENTIFIER = ['et_home_slider', 'et_home_slider_mm', 'home_slider_one', 'home_slider_one_mm'];

    /**
     * @var HomeSliderGenerator
     */
    private $homeSliderHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var HomeSliderCollection
     */
    private $homeSliderCollection;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /** 
     * @var Filesystem
     */
    protected $_filesystem;

    /** 
     * @var AdapterFactory
     */
    protected $_imageFactory;

    public function __construct(
        HomeSliderGenerator $homeSliderGenerator,
        StoreManagerInterface $storeManagerInterface,
        HomeSliderCollection $homeSliderCollection,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepository $pageRepository,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        Filesystem $_filesystem,
        AdapterFactory $_imageFactory
    ) {
        $this->homeSliderHelper = $homeSliderGenerator;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->homeSliderCollection = $homeSliderCollection;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageRepository = $pageRepository;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->_filesystem = $_filesystem;
        $this->_imageFactory = $_imageFactory;
    }

    public function getHomeSlider($type)
    {
        $blockContent = '';
        $result = [];
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $websiteId = $currentStore->getWebsiteId();
        $code = $currentStore->getCode();
        if (str_ends_with($code, 'mm')) {
            if ($type == 1) {
                $blockContent = $this->homeSliderHelper->getBlockContent($websiteId, 'identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[3]);
            } else {
                $blockContent = $this->homeSliderHelper->getBlockContent($websiteId, 'identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[1]);
            }
        } else {
            if ($type == 1) {
                $blockContent = $this->homeSliderHelper->getBlockContent($websiteId, 'identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[2]);
            } else {
                $blockContent = $this->homeSliderHelper->getBlockContent($websiteId);
            }
        }

        echo($blockContent->getContent());
        if (isset($blockContent)) {
            $blockContent = $blockContent->getContent();
            $ids = $this->getHomeSliderIdsToSort($blockContent);
            echo(' ids . ' . json_encode($ids));
            if (count($ids) > 0) {
                $collection = $this->homeSliderCollection->create()
                ->addFieldToFilter('is_active', ['eq' => 1]);
            $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(main_table.homeslider_id,' . "'" . implode(',', $ids) . "'" . ')'));
            foreach ($collection as $item) {
                $existedSkuList[] = $item['sku'];
                $data = [];
                $data['web_img'] = $baseUrl . 'homeslider/images/image' . $item['home_slider_image'];
                try {
                    $mobileImage = $this->resize($item['home_slider_image_mobile_app'], 1400, 700);
                } catch (Exception $e) {
                    $mobileImage = $baseUrl . 'homeslider/images/image' . $item['home_slider_image_mobile_app'];
                }
                $data['mobile_img'] = $mobileImage;
                if ($item['category_id']) {
                    $data['id'] = (int) $item['category_id'];
                    $data['type'] = 1;
                    try {
                        $category = $this->categoryRepositoryInterface->get((int) $item['category_id']);
                        $data['name'] = $category->getName();
                    } catch (NoSuchEntityException $e) {
                        $data['name'] = '';
                    }
                } else {

                    $filteredId = $this->_filterBuilder
                        ->setConditionType('eq')
                        ->setField('identifier')
                        ->setValue('hm__000__' . $item['homeslider_id'])
                        ->create();
                    $filterGroupList = [];
                    $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                    $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
                    $pageList = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
                    if ($pageList->getTotalCount() > 0) {
                        $data['type'] = 2;
                        $data['id'] = (int) $item['homeslider_id'];
                        $data['name'] = '';
                    } else {
                        $data['type'] = 0;
                        $data['id'] = 0;
                        $data['name'] = '';
                    }
                }
                $result[] = $data;
            }
            }
        }
        return $result;
    }

    /**
     * get all home slider ids list
     * @param String $content
     * @return array
     */
    private function getHomeSliderIdsToSort($content)
    {
        $ids = [];
        $needle = "promo-";
        $lastPos = 0;
        $positions = array();

        while (($lastPos = strpos($content, $needle, $lastPos)) !== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }

        // Displays 3 and 10
        foreach ($positions as $value) {
            try {
                preg_match_all('/[0-9]+/', substr($content, $value + strlen("promo-"), 3), $matches);
                $ids[] = $matches[0][0];
            } catch (Exception $e) {
            }
        }
        return $ids;
    }


    // pass imagename, width and height
    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('homeslider/images/image/') . $image;
        if (!file_exists($absolutePath))
            return false;
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('homeslider/mobile/' . $width . '/') . $image;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(TRUE);
            $imageResize->resize($width, $height);
            //destination folder                
            $destination = $imageResized;
            //save image      
            $imageResize->save($destination);
        }
        $resizedURL = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'homeslider/mobile/' . $width . '/' . $image;
        return $resizedURL;
    }
}
