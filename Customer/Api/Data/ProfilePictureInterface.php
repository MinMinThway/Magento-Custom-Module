<?php

namespace MMT\Customer\Api\Data;

interface ProfilePictureInterface
{

    const TYPE = 'type';
    const NAME = 'name';
    const BASE64_ENCODED_DATA = 'base64_encoded_data';

    /**
     * Get type
     * @return string|null
     */
    public function getType();

    /**
     * Set type
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get Name
     * @return string|null
     */
    public function getName();

    /**
     * Set Name
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get base64 encoded data
     * @return string|null
     */
    public function getBase64EncodedData();

    /**
     * Set base64 encoded data
     * @param string $imageData
     * @return $this
     */
    public function setBase64EncodedData($imageData);

}
