<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Review;

use Magento\Framework\Registry;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class ReviewFixtureRollback
{
    /**
     * @var Registry
     */
    private readonly Registry $registry;
    /**
     * @var ReviewResourceModel
     */
    private readonly ReviewResourceModel $reviewResourceModel;

    /**
     * @param Registry $registry
     * @param ReviewResourceModel $reviewResourceModel
     */
    public function __construct(
        Registry $registry,
        ReviewResourceModel $reviewResourceModel,
    ) {
        $this->registry = $registry;
        $this->reviewResourceModel = $reviewResourceModel;
    }

    /**
     * @return ReviewFixtureRollback
     */
    public static function create(): ReviewFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(ReviewResourceModel::class),
        );
    }

    /**
     *  Roll back reviews.
     *
     * @param ReviewFixture ...$reviewFixtures
     *
     * @return void
     * @throws \Exception
     */
    public function execute(ReviewFixture ...$reviewFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($reviewFixtures as $reviewFixture) {
            $this->reviewResourceModel->delete(
                $reviewFixture->getReview(),
            );
        }

        $this->registry->unregister('isSecureArea');
    }
}
