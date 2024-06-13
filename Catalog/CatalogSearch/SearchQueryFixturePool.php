<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\CatalogSearch;

use Magento\Search\Model\QueryInterface;

class SearchQueryFixturePool
{
    /**
     * @var SearchQueryFixture[]
     */
    private array $autoCompleteFixtures = [];

    /**
     * @param QueryInterface $query
     * @param string|null $key
     *
     * @return void
     */
    public function add(QueryInterface $query, ?string $key = null): void
    {
        if ($key === null) {
            $this->autoCompleteFixtures[] = new SearchQueryFixture($query);
        } else {
            $this->autoCompleteFixtures[$key] = new SearchQueryFixture($query);
        }
    }

    /**
     * Returns store fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return SearchQueryFixture
     */
    public function get(?string $key = null): SearchQueryFixture
    {
        if ($key === null) {
            $key = array_key_last($this->autoCompleteFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->autoCompleteFixtures)) {
            throw new \OutOfBoundsException('No matching auto complete found in fixture pool');
        }

        return $this->autoCompleteFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        SearchQueryFixtureRollback::create()->execute(...array_values($this->autoCompleteFixtures));
        $this->autoCompleteFixtures = [];
    }
}
