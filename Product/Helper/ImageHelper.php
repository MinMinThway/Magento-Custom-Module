<?php

namespace MMT\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;

class ImageHelper extends AbstractHelper
{

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var AdapterFactory 
     */
    private $adapterFactory;

    /**
     * @var StoreManagerInterface 
     */
    private $storeManagerInterface;

    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->filesystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }


    /**
     * resize image
     * @param string $fileName
     * @param string $rDir
     * @param string $wPath
     * @param int $width
     * @param int @height
     */
    public function resize($fileName, $rDir, $wPath, $width = 194, $height = 194)
    {
        $absolutePath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($rDir) . $fileName;
        if (!file_exists($absolutePath))
            return false;
        $imageResized = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($wPath . $width . '/') . $fileName;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->adapterFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(TRUE);
            $imageResize->resize($width, $height);
            //destination folder                
            $destination = $imageResized;
            //save image      
            $imageResize->save($destination);
        }
        $resizedURL = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $wPath . $width . '/' . $fileName;
        return $resizedURL;
    }
}
