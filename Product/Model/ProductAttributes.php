<?php

namespace MMT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Product\Api\Data\ProductAttributesInterface;
class ProductAttributes extends AbstractExtensibleModel implements ProductAttributesInterface
{
    
    /**
     * @inheritdoc
    */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritdoc
    */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL,$label);
    }

    /**
     * @inheritdoc
    */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritdoc
    */
    public function setValue($value)
    {
        return $this->setData(self::VALUE,$value);
    }
    
}
