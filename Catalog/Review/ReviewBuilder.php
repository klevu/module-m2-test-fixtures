<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Review;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Review\Model\Rating;
use Magento\Review\Model\Rating\Option;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating as RatingResourceModel;
use Magento\Review\Model\ResourceModel\Rating\Option\Collection as RatingOptionCollection;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Magento\Review\Model\Review;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\IndexFailed;

class ReviewBuilder
{
    /**
     * @var Review
     */
    private Review $review;
    /**
     * @var ReviewResourceModel
     */
    private readonly ReviewResourceModel $reviewResourceModel;
    /**
     * @var RatingOptionCollection
     */
    private readonly RatingOptionCollection $ratingOptionsCollection;
    /**
     * @var RatingFactory
     */
    private readonly RatingFactory $ratingFactory;
    /**
     * @var RatingResourceModel
     */
    private readonly RatingResourceModel $ratingResourceModel;
    /**
     * @var array<int, int>
     */
    private array $ratings = [];

    /**
     * @param Review $review
     * @param ReviewResourceModel $reviewResourceModel
     * @param RatingOptionCollection $ratingOptionsCollection
     * @param RatingFactory $ratingFactory
     * @param RatingResourceModel $ratingResourceModel
     */
    public function __construct(
        Review $review,
        ReviewResourceModel $reviewResourceModel,
        RatingOptionCollection $ratingOptionsCollection,
        RatingFactory $ratingFactory,
        RatingResourceModel $ratingResourceModel,
    ) {
        $this->review = $review;
        $this->reviewResourceModel = $reviewResourceModel;
        $this->ratingOptionsCollection = $ratingOptionsCollection;
        $this->ratingFactory = $ratingFactory;
        $this->ratingResourceModel = $ratingResourceModel;
    }

    /**
     * @return ReviewBuilder
     */
    public static function aProductReview(): ReviewBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        $review = $objectManager->create(Review::class);
        $review->setEntityId(
            $review->getEntityIdByCode(entityCode: Review::ENTITY_PRODUCT_CODE),
        );

        return new static(
            review: $review,
            reviewResourceModel: $objectManager->create(ReviewResourceModel::class),
            ratingOptionsCollection: $objectManager->create(RatingOptionCollection::class),
            ratingFactory: $objectManager->create(RatingFactory::class),
            ratingResourceModel: $objectManager->create(RatingResourceModel::class),
        );
    }

    /**
     * @param int $productId
     *
     * @return ReviewBuilder
     */
    public function withProductId(int $productId): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setEntityPkValue($productId);

        return $builder;
    }

    /**
     * @param int|null $customerId
     *
     * @return $this
     */
    public function withCustomerId(?int $customerId): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setCustomerId($customerId);

        return $builder;
    }

    /**
     * @param string $nickname
     *
     * @return ReviewBuilder
     */
    public function withCustomerNickname(string $nickname): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setNickname($nickname);

        return $builder;
    }

    /**
     * @param int $storeId
     *
     * @return ReviewBuilder
     */
    public function withStoreId(int $storeId): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setStoreId($storeId);

        return $builder;
    }

    /**
     * @param mixed $stores
     *
     * @return $this
     */
    public function withStores(mixed $stores): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setStores($stores);

        return $builder;
    }

    /**
     * @param int $statusId
     *
     * @return ReviewBuilder
     */
    public function withStatus(int $statusId): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setStatusId($statusId);

        return $builder;
    }

    /**
     * @param string $title
     *
     * @return ReviewBuilder
     */
    public function withTitle(string $title): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setTitle($title);

        return $builder;
    }

    /**
     * @param string $detail
     *
     * @return $this
     */
    public function withDetail(string $detail): ReviewBuilder
    {
        $builder = clone $this;
        $builder->review->setDetail($detail);

        return $builder;
    }

    /**
     * @param array<int, int> $ratings
     *
     * @return ReviewBuilder
     */
    public function withRatings(array $ratings): ReviewBuilder
    {
        $builder = clone $this;
        $builder->ratings = $ratings;

        return $builder;
    }

    /**
     * @return Review
     * @throws \Exception
     */
    public function build(): Review
    {
        try {
            $review = $this->createReview();
        } catch (\Exception $e) {
            $e->getPrevious();
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }

        return $review;
    }

    /**
     * @return Review
     * @throws AlreadyExistsException
     */
    private function createReview(): Review
    {
        $builder = clone $this;
        $builder->reviewResourceModel->save($builder->review);

        /** @var Option[] $allOptions */
        $allOptions = $this->ratingOptionsCollection->getItems();
        $options = array_filter(
            array: $allOptions,
            callback: static fn (Option $option) => (
                array_key_exists(key: (int)$option->getData('rating_id'), array: $builder->ratings)
                && $builder->ratings[(int)$option->getData('rating_id')] === (int)$option->getData('value')
            ),
        );

        foreach ($options as $option) {
            /** @var Rating $rating */
            $rating = $this->ratingFactory->create();
            $rating->setRatingId($option->getData('rating_id'));
            $rating->setReviewId($builder->review->getId());
            $rating->setStores($builder->review->getStores());
            $entityPkValue = $builder->review->getEntityPkValue();
            $rating->addOptionVote($option->getData('option_id'), $entityPkValue);
            $this->ratingResourceModel->save($rating);
        }
        $builder->review->aggregate();

        return $builder->review;
    }

    /**
     * @param \Throwable|null $exception
     * @return bool
     */
    private static function isTransactionException(?\Throwable $exception): bool
    {
        if ($exception === null) {
            return false;
        }
        return (bool) preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
