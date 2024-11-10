<?php

namespace MMT\Product\Model\Api;

use DateTime;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order;
use MMT\Product\Api\CustomRatingAndReviewInterface;
use MMT\Product\Api\Data\ReviewManagementSearchResultsInterfaceFactory;
use MMT\Product\Model\CustomKeyValFactory;
use MMT\Product\Model\ReviewManagementFactory;

class CustomRatingAndReview implements CustomRatingAndReviewInterface
{

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var ReviewManagementFactory
     */
    private $reviewManagementFactory;

    /**
     * @var ReviewManagementSearchResultsInterfaceFactory
     */
    private $reviewManagementSearchResultsInterfaceFactory;

    /**
     * @var CustomKeyValFactory
     */
    private $customKeyValFactory;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    public function __construct(
        \Magento\Review\Model\RatingFactory $ratingFactory,
        StoreManagerInterface $storeManagerInterface,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        ProductFactory $productFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        ReviewManagementFactory $reviewManagementFactory,
        ReviewManagementSearchResultsInterfaceFactory $reviewManagementSearchResultsInterfaceFactory,
        CustomKeyValFactory $customKeyValFactory,
        ManagerInterface $managerInterface,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->reviewFactory = $reviewFactory;
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->objectFactory = $objectFactory;
        $this->reviewManagementFactory = $reviewManagementFactory;
        $this->reviewManagementSearchResultsInterfaceFactory = $reviewManagementSearchResultsInterfaceFactory;
        $this->customKeyValFactory = $customKeyValFactory;
        $this->managerInterface = $managerInterface;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getRatingList()
    {
        $ratingCollection = $this->ratingFactory->create()
            ->getResourceCollection()
            ->addFieldToSelect(array('rating_id', 'rating_code'))
            ->addEntityFilter(
                'product'
            )->setPositionOrder()->setStoreFilter(
                $this->storeManagerInterface->getStore()->getId()
            )->addRatingPerStoreName(
                $this->storeManagerInterface->getStore()->getId()
            )->load();

        $ratingCollection->getSelect()->join('rating_option as viewed_item', 'main_table.rating_id = viewed_item.rating_id', 'option_id');    // Add this join statement
        $ratingCollection->getSelect()
            ->order('main_table.rating_id ASC')
            ->order('viewed_item.position ASC');

        $result = [];
        foreach ($ratingCollection->getData() as $rating) {
            $result[$rating['rating_id']][] = $rating;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function submitReview($customerId)
    {
        $data = $this->request->getBodyParams();
        $obj = $this->objectFactory->create();
        $rating = [];
        if (isset($data['ratings'])) {
            $rating = $data['ratings'];
        }

        /** @var \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create()->setData($data);
        $review->unsetData('review_id');
        $validate = $this->validate($review);
        if ($validate === true && isset($data['productId'])) {
            if ($this->productFactory->create()->load($data['productId'])->getId()) {
                try {
                    $canSubmit = $this->canSubmitReview($customerId, $data['productId']);
                    if (!$canSubmit) {
                        $obj->setItem(array('success' => false, 'message' => 'You can only submit review after purchasing this item.'));
                        return $obj->getData();
                    }
                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($data['productId'])
                        ->setStatusId(Review::STATUS_PENDING)
                        ->setCustomerId($customerId)
                        ->setStoreId($this->storeManagerInterface->getStore()->getId())
                        ->setStores([$this->storeManagerInterface->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($optionId, $data['productId']);
                    }

                    $review->aggregate();
                    $obj->setItem(array('success' => true, 'message' => 'You submitted your review for moderation.'));
                    $this->managerInterface->dispatch('review_save_after', [
                        'data_object'        => $review
                    ]);
                    return $obj->getData();
                } catch (\Exception $e) {
                    $obj->setItem(array('success' => false, 'message' => 'We can\'t post your review right now.'));
                    return $obj->getData();
                }
            }
        }
        $obj->setItem(array('success' => false, 'message' => 'We can\'t post your review right now.'));
        return $obj->getData();
    }

    /**
     * @inheritdoc
     */
    public function getProductReviewListByCustomerId($customerId, SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('detail.customer_id', $customerId)->load();
        $collection->getSelect()->joinInner(['vote' => 'rating_option_vote'], 'main_table.review_id = vote.review_id', '*');
        $collection->getSelect()->joinInner(['rating' => 'rating'], 'vote.rating_id = rating.rating_id', 'rating_code');
        $collection->addFieldToFilter('rating.rating_code', 'Quality');
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->getCurPage($searchCriteria->getCurrentPage());
        $collection->setOrder('main_table.review_id');
        $collection->load();
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();

        $searchResult = $this->reviewManagementSearchResultsInterfaceFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        $reviewList = [];
        foreach ($collection->getData() as $review) {
            if (
                isset($review['review_id']) && isset($review['created_at']) && isset($review['title']) &&
                isset($review['detail']) && isset($review['percent']) && isset($review['entity_pk_value']) &&
                isset($review['rating_code'])
            ) {
                $reviewData = $this->reviewManagementFactory->create();
                $reviewData->setReviewId($review['review_id']);
                $date = new DateTime($review['created_at']);
                $reviewData->setCreatedAt($date->format('d/m/Y'));
                $reviewData->setTitle($review['title']);
                $reviewData->setSummary($review['detail']);
                $reviewData->setProductId($review['entity_pk_value']);
                $rating = $this->customKeyValFactory->create();
                $rating->setKey($review['rating_code']);
                $rating->setValue($review['percent']);
                $reviewData->setRating([$rating]);

                $productAttr = $this->getProductAndRatingData($reviewData->getProductId(), $baseUrl);
                if (
                    count($productAttr) == 4 && isset($productAttr['name']) && isset($productAttr['imgPath']) &&
                    isset($productAttr['average']) && isset($productAttr['counts'])
                ) {
                    $reviewData->setProductName($productAttr['name']);
                    $reviewData->setProductImgPath($productAttr['imgPath']);
                    $reviewData->setAverageRating($productAttr['average']);
                    $reviewData->setNoOfReviews($productAttr['counts']);
                }
                $reviewList[] = $reviewData;
            }
        }
        $searchResult->setItems($reviewList);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function getReviewByIdAndCustomerId($customerId, $reviewId)
    {
        $collection = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('detail.customer_id', $customerId)->load();
        $collection->getSelect()->joinInner(['vote' => 'rating_option_vote'], 'main_table.review_id = vote.review_id', '*');
        $collection->getSelect()->joinInner(['rating' => 'rating'], 'vote.rating_id = rating.rating_id', 'rating_code');
        $collection->addFieldToFilter('main_table.review_id', $reviewId);
        $collection->setOrder('main_table.review_id');
        $collection->load();
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        $reviewData = $this->reviewManagementFactory->create();

        foreach ($collection->getData() as $review) {
            if (
                isset($review['review_id']) && isset($review['created_at']) && isset($review['title']) &&
                isset($review['detail']) && isset($review['percent']) && isset($review['entity_pk_value']) &&
                isset($review['rating_code'])
            ) {
                $reviewData->setReviewId($review['review_id']);
                $date = new DateTime($review['created_at']);
                $reviewData->setCreatedAt($date->format('d/m/Y'));
                $reviewData->setTitle($review['title']);
                $reviewData->setSummary($review['detail']);
                $reviewData->setProductId($review['entity_pk_value']);
                $rating = $this->customKeyValFactory->create();
                $rating->setKey($review['rating_code']);
                $rating->setValue($review['percent']);
                if ($reviewData->getRating()) {
                    $ratingArr = $reviewData->getRating();
                    $ratingArr[] = $rating;
                    $reviewData->setRating($ratingArr);
                } else {
                    $reviewData->setRating([$rating]);
                }

                $productAttr = $this->getProductAndRatingData($reviewData->getProductId(), $baseUrl);
                if (
                    count($productAttr) == 4 && isset($productAttr['name']) && isset($productAttr['imgPath']) &&
                    isset($productAttr['average']) && isset($productAttr['counts'])
                ) {
                    $reviewData->setProductName($productAttr['name']);
                    $reviewData->setProductImgPath($productAttr['imgPath']);
                    $reviewData->setAverageRating($productAttr['average']);
                    $reviewData->setNoOfReviews($productAttr['counts']);
                }
            }
        }
        if (!$reviewData->getId()) {
            throw new NoSuchEntityException(__('Review with id "%1" does not exist.', $reviewId));
        }
        return $reviewData;
    }

    /**
     * get product name and image url by product id
     * @param int $productId
     * @param string $baseUrl
     * @return array
     */
    private function getProductAndRatingData($productId, $baseUrl)
    {
        $product = $this->productFactory->create()->load($productId);
        $productAttr = [];
        if ($product->getId()) {
            $productAttr = $this->getProductAvgRatingAndReviewCounts($product);
            if ($product->getCustomAttributes()) {
                $productAttr['name'] = $product->getName();
                foreach ($product->getCustomAttributes() as $customAttribute) {
                    if ($customAttribute->getAttributeCode() == 'image') {
                        $productAttr['imgPath'] = $baseUrl . 'media/catalog/product' .  $customAttribute->getValue();
                    }
                }
            }
        }
        return $productAttr;
    }

    /**
     * get product average rating and review counts
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getProductAvgRatingAndReviewCounts($product)
    {
        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManagerInterface->getStore()->getId());
        return array('average' => $product->getRatingSummary()->getRatingSummary(), 'counts' => $product->getRatingSummary()->getReviewsCount());
    }

    /**
     * Validate review summary fields
     * @param \Magento\Review\Model\Review $review
     * @return bool|string[]
     */
    private function validate($review)
    {
        $errors = [];
        if (!\Zend_Validate::is($review->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a nickname.');
        }

        if (!\Zend_Validate::is($review->getDetail(), 'NotEmpty')) {
            $errors[] = __('Please enter a review.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * check customer already bought specific product and order also was completed
     * @param int $customerId
     * @param int $productId
     * @return bool
     */
    private function canSubmitReview($customerId, $productId)
    {

        $orders = $this->orderCollectionFactory
            ->create()
            ->addFieldToFilter(
                'customer_id',
                $customerId
            )
            ->addFieldToFilter(
                'status',
                'complete'
            )
            ->addFieldToFilter(
                'state',
                'complete'
            )
            ->setOrder(
                'created_at',
                'desc'
            );

        if ($orders->getSize() > 0) {
            foreach ($orders as $order) {
                /** @var Order $order */
                foreach ($order->getAllItems() as $item) {
                    if (
                        $item->getProductId() == $productId &&
                        $item->getQtyOrdered() > $item->getQtyRefunded()
                    ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

