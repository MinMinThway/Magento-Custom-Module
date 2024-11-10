<?php

namespace MMT\Cart\Api\Data;

interface DeliveryDateOptionsInterface{
    const OPTION_TITLE ='option_title';
    const OPTION_VALUE = 'option_value';

    /**
     * get delivery date option title
     * @return string
     */
    public function getOptionTitle();

    /**
     * set delivery date option title
     * @param bool $optionTitle
     * @return $this
     */
    public function setOptionTitle($optionTitle);

    /**
     * get delivery date option value
     * @return string
     */
    public function getOptionValue();

    /**
     * set delivery date option value
     * @param string $optionValue
     * @return $this
     */
    public function setOptionValue($optionValue);
}