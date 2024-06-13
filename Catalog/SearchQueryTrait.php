<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\CatalogSearch\SearchQueryBuilder;
use Klevu\TestFixtures\Catalog\CatalogSearch\SearchQueryFixturePool;
use Magento\Store\Model\Store;

trait SearchQueryTrait
{
    /**
     * @var SearchQueryFixturePool|null
     */
    private ?SearchQueryFixturePool $searchQueryFixturePool = null;

    /**
     * @param mixed[]|null $queryData
     *
     * @return void
     * @throws \Exception
     */
    public function createSearchQuery(?array $queryData = []): void
    {
        $builder = SearchQueryBuilder::aQuery();

        $builder = $builder->withQueryText(queryText: $queryData['query_text'] ?? 'query text');
        $builder = $builder->withNumResults(numberOfResults: $queryData['number_of_results'] ?? 1);
        $builder = $builder->withPopularity(popularity: $queryData['popularity'] ?? 100);
        $builder = $builder->withDisplayInTerms(displayInTerms: (int)(bool)($queryData['display_in_terms'] ?? 1));
        $builder = $builder->withIsActive(isActive: (int)(bool)($queryData['is_active'] ?? 1));
        $builder = $builder->withIsProcessed(isProcessed: (int)(bool)($queryData['is_processed'] ?? 1));
        $builder = $builder->withStoreId(storeId: $queryData['store_id'] ?? Store::DISTRO_STORE_ID);

        $this->searchQueryFixturePool->add(
            query: $builder->build(),
            key: $ruleData['key'] ?? 'test_query',
        );
    }
}
