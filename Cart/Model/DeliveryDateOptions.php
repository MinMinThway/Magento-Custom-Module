<?php

namespace MMT\Cart\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Cart\Api\Data\DeliveryDateOptionsInterface;

class DeliveryDateOptions extends AbstractExtensibleModel implements DeliveryDateOptionsInterface
{
    /**
     * @inheritdoc
     */
    public function getOptionTitle()
    {
        return $this->getData(self::OPTION_TITLE);
    }

    /**
     * @inheritdoc
    */
    public function setOptionTitle($optionTitle)
    {
        return $this->setData(self::OPTION_TITLE,$optionTitle);
    }    

     /**
     * @inheritdoc
     */
    public function getOptionValue()
    {
        return $this->getData(self::OPTION_VALUE);
    }

     /**
     * @inheritdoc
     */
    public function setOptionValue($optionValue)
    {
        return $this->setData(self::OPTION_VALUE,$optionValue);
    }
    
}
