<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\CatalogSearch;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\ResourceModel\Query as QueryResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class SearchQueryFixtureRollback
{
    /**
     * @var Registry
     */
    private readonly Registry $registry;
    /**
     * @var QueryResourceModel
     */
    private readonly QueryResourceModel $queryResourceModel;

    /**
     * @param Registry $registry
     * @param QueryResourceModel $queryResourceModel
     */
    public function __construct(
        Registry $registry,
        QueryResourceModel $queryResourceModel,
    ) {
        $this->registry = $registry;
        $this->queryResourceModel = $queryResourceModel;
    }

    /**
     * @return SearchQueryFixtureRollback
     */
    public static function create(): SearchQueryFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(QueryResourceModel::class),
        );
    }

    /**
     * Roll back attributes.
     *
     * @param SearchQueryFixture ...$searchQueryFixtures
     *
     * @throws \Exception
     */
    public function execute(SearchQueryFixture ...$searchQueryFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        foreach ($searchQueryFixtures as $searchQueryFixture) {
            /** @var AbstractModel|QueryInterface $searchQuery */
            $searchQuery = $searchQueryFixture->getQuery();
            $this->queryResourceModel->delete($searchQuery);
        }
        $this->registry->unregister('isSecureArea');
    }
}
