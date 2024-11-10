<?php

namespace MMT\Country\Api\Data;

use Magento\Directory\Api\Data\RegionInformationInterface;

interface CustomRegionInfoInterface extends RegionInformationInterface
{

    /**
    * set city list
    * @param \MMT\Country\Api\Data\CustomCityListInterface[] $cityList
    * @return $this
    */
    public function setCityList(array $cityList=null);

    /**
    * get city list
    * @return \MMT\Country\Api\Data\CustomCityListInterface[]|[]
    */
    public function getCityList();
}
