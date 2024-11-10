<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MMT\HomeSlider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HomeSliderRepositoryInterface
{

    /**
     * Save HomeSlider
     * @param \MMT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
     * @return \MMT\HomeSlider\Api\Data\HomeSliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MMT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
    );

    /**
     * Retrieve HomeSlider
     * @param string $homesliderId
     * @return \MMT\HomeSlider\Api\Data\HomeSliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($homesliderId);

    /**
     * Retrieve HomeSlider matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MMT\HomeSlider\Api\Data\HomeSliderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete HomeSlider
     * @param \MMT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MMT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
    );

    /**
     * Delete HomeSlider by ID
     * @param string $homesliderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($homesliderId);
}

