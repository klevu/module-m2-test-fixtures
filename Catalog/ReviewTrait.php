<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\Review\ReviewBuilder;
use Klevu\TestFixtures\Catalog\Review\ReviewFixturePool;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\Review;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

trait ReviewTrait
{
    /**
     * @var ReviewFixturePool|null
     */
    private mixed $reviewFixturePool = null;

    /**
     * @param mixed[]|null $reviewData
     *
     * @return void
     * @throws \Exception
     */
    public function createReview(?array $reviewData): void
    {
        $reviewBuilder = ReviewBuilder::aProductReview();

        $reviewBuilder = $reviewBuilder->withProductId(
            productId: $reviewData['product_id'],
        );
        $reviewBuilder = $reviewBuilder->withCustomerId(
            customerId: $reviewData['customer_id'] ?? null,
        );
        $reviewBuilder = $reviewBuilder->withCustomerNickname(
            nickname: $reviewData['nickname'] ?? 'The Reviewer',
        );
        $reviewBuilder = $reviewBuilder->withStoreId(
            storeId: $reviewData['store_id'] ?? Store::DEFAULT_STORE_ID,
        );
        $reviewBuilder = $reviewBuilder->withStores(
            stores: $reviewData['stores'] ?? $reviewData['store_id'],
        );
        $reviewBuilder = $reviewBuilder->withStatus(
            statusId: $reviewData['status'] ?? Review::STATUS_APPROVED,
        );
        $reviewBuilder = $reviewBuilder->withTitle(
            title: $reviewData['title'] ?? sprintf('A review for Product ID %s', $reviewData['product_id']),
        );
        $reviewBuilder = $reviewBuilder->withDetail(
            detail: $reviewData['detail'] ?? 'This is a good thing.',
        );
        $reviewBuilder = $reviewBuilder->withRatings(
            ratings: $reviewData['ratings'] ?? null,
        );

        $this->reviewFixturePool->add(
            review: $reviewBuilder->build(),
            key: 'test_review',
        );
    }

    /**
     * @return int[]
     */
    public function getRatingIds(): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $ratingCollection = $objectManager->get(RatingCollection::class);
        $ratings = $ratingCollection->getItems();
        $return = [];
        foreach ($ratings as $rating) {
            $return[] = (int)$rating->getData('rating_id');
        }

        return $return;
    }
}
