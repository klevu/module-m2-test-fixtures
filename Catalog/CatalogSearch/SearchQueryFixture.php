<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\CatalogSearch;

use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\Search\Model\QueryInterface;

class SearchQueryFixture
{
    /**
     * @var QueryInterface
     */
    private QueryInterface $query;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }

    /**
     * @return QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getQueryText(): string
    {
        return $this->query->getQueryText();
    }

    /**
     * @return void
     * @throws InvalidModelException
     */
    public function rollback(): void
    {
        SearchQueryFixtureRollback::create()->execute($this);
    }
}
