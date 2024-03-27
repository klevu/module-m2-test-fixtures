<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Review;

use Magento\Review\Model\Review;

class ReviewFixturePool
{
    /**
     * @var ReviewFixture[]
     */
    private array $reviewFixtures = [];

    public function add(Review $review, ?string $key): void
    {
        if ($key === null) {
            $this->reviewFixtures[] = new ReviewFixture($review);
        } else {
            $this->reviewFixtures[$key] = new ReviewFixture($review);
        }
    }

    /**
     * Returns review fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return ReviewFixture
     */
    public function get(?string $key = null): ReviewFixture
    {
        if ($key === null) {
            $key = array_key_last($this->reviewFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->reviewFixtures)) {
            throw new \OutOfBoundsException('No matching review found in fixture pool');
        }

        return $this->reviewFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        ReviewFixtureRollback::create()->execute(...array_values($this->reviewFixtures));
        $this->reviewFixtures = [];
    }
}
