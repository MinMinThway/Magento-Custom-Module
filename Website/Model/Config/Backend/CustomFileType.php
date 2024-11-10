<?php

namespace MMT\Website\Model\Config\Backend;

class CustomFileType extends \Magento\Config\Model\Config\Backend\File
{

    /**
     * Upload max file size in kilobytes
     *
     * @var int
     */
    protected $_maxFileSize = 600;

    /**
     * @return string[]
     */
    public function getAllowedExtensions() {
        return ['jpg', 'jpeg', 'png'];
    }
}