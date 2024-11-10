<?php

namespace MMT\HomeSlider\Helper;

use MMT\CatalogRule\Helper\CustomDataUpdater;
use MMT\CatalogRule\Helper\HomeSliderGenerator;

class HomeSliderGenerate
{

    /**
     * @var CustomDataUpdater 
     */
    protected $customDataUpdater;

    /**
     * @var HomeSliderGenerator
     */
    protected $homeSliderGenerator;

    public function __construct(
        CustomDataUpdater $customDataUpdater,
        HomeSliderGenerator $homeSliderGenerator
    ) {
        $this->customDataUpdater = $customDataUpdater;
        $this->homeSliderGenerator = $homeSliderGenerator;
    }

    /**
     * Generate home slider and call helper method at CatalogRule Rule
     * @param int $id
     * @param string $dataType
     * @param int $categoryId
     * @param int $websiteId
     * @param string $url
     */
    public function homeSliderGenerate($id, $dataType, $categoryId, $websiteId, $url)
    {

        $this->customDataUpdater->updateData($id, $dataType, $categoryId, $websiteId, $url);
    }
}