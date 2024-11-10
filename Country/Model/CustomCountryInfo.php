<?php

namespace MMT\Country\Model;

use Magento\Directory\Model\Data\CountryInformation;
use MMT\Country\Api\Data\CustomCountryInfoInterface;

class CustomCountryInfo extends CountryInformation implements CustomCountryInfoInterface
{
}
