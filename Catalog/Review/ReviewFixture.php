<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Review;

use Magento\Review\Model\Review;

class ReviewFixture
{
    /**
     * @var Review
     */
    private Review $review;

    /**
     * @param Review $review
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    /**
     * @return Review
     */
    public function getReview(): Review
    {
        return $this->review;
    }

    /**
     * @return int
     */
    public function getReviewId(): int
    {
        return (int)$this->review->getId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        ReviewFixtureRollback::create()->execute($this);

    }
}
