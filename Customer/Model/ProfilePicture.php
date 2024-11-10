<?php

namespace MMT\Customer\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Customer\Api\Data\ProfilePictureInterface;

class ProfilePicture extends AbstractExtensibleModel implements ProfilePictureInterface
{

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setBase64EncodedData($imageData)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $imageData);
    }

    /**
     * @inheritdoc
     */
    public function getBase64EncodedData()
    {
        return $this->getData(self::BASE64_ENCODED_DATA);
    }

}
