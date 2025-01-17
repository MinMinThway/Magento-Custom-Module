<?php

namespace MMT\Delivery\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;

class UploadFile extends File
{
    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['csv', 'xls'];
    }
}
