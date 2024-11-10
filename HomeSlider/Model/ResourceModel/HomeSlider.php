<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MMT\HomeSlider\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class HomeSlider extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('mit_homeslider_homeslider', 'homeslider_id');
    }

    /**
     * @param $homesliderId
     */
    public function deleteActionIndex($homesliderId)
    {
        $this->deleteMultipleData('mit_homeslider_homeslider', ['homeslider_id = ?' => $homesliderId]);
    }

    /**
     * @param $data
     */
    public function insertActionIndex($data)
    {
        $this->updateMultipleData('mit_homeslider_homeslider', $data);
    }

    /**
     * delete database
     *
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteMultipleData($tableName, $where = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($where)) {
            $this->getConnection()->delete($table, $where);
        }
    }

    /**
     * update database
     *
     * @param string $tableName
     * @param array $data
     *
     * @return void
     */
    public function updateMultipleData($tableName, $data = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($data)) {
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
}