<?php

namespace MMT\ProductSorter\Plugin\Magento\Catalog\Model;

class Config
{

    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $subject,
        $result
    ) {

        $modifiedResult = [];
        foreach ($result as $key => $val) {
            if ($key === 'price') {
                $modifiedResult[$key . '_asc'] = $val . ': Low to high ';
                $modifiedResult[$key . '_desc'] = $val . ': High to low ';
            } else if ($key === 'name') {
                continue;
            } else {
                if ($key === 'position') {
                    $val = 'Best Match';
                }
                $modifiedResult[$key] = $val;
            }
        }
        return $modifiedResult;
    }
}

