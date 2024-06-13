<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\CatalogSearch;

// phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\ResourceModel\Query as QueryResourceModel;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\IndexFailed;

class SearchQueryBuilder
{
    /**
     * @var Query&QueryInterface
     */
    private QueryInterface $query;
    /**
     * @var QueryResourceModel
     */
    private QueryResourceModel $queryResourceModel;

    /**
     * @param QueryInterface $query
     * @param QueryResourceModel $queryResourceModel
     */
    public function __construct(
        QueryInterface $query,
        QueryResourceModel $queryResourceModel,
    ) {
        $this->query = $query;
        $this->queryResourceModel = $queryResourceModel;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->query = clone $this->query;
    }

    /**
     * @return SearchQueryBuilder
     */
    public static function aQuery(): SearchQueryBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var QueryInterface $query */
        $query = $objectManager->create(QueryInterface::class);
        $queryResourceModel = $objectManager->create(QueryResourceModel::class);

        return new static(
            $query,
            $queryResourceModel,
        );
    }

    /**
     * @param string $queryText
     *
     * @return $this
     */
    public function withQueryText(string $queryText): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setQueryText($queryText);

        return $builder;
    }

    /**
     * @param int $numberOfResults
     *
     * @return $this
     */
    public function withNumResults(int $numberOfResults): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setNumResults($numberOfResults);

        return $builder;
    }

    /**
     * @param int $popularity
     *
     * @return $this
     */
    public function withPopularity(int $popularity): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setPopularity($popularity);

        return $builder;
    }

    /**
     * @param int $displayInTerms
     *
     * @return $this
     */
    public function withDisplayInTerms(int $displayInTerms): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setDisplayInTerms($displayInTerms);

        return $builder;
    }

    /**
     * @param int $isActive
     *
     * @return $this
     */
    public function withIsActive(int $isActive): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setIsActive($isActive);

        return $builder;
    }

    /**
     * @param int $isProcessed
     *
     * @return $this
     */
    public function withIsProcessed(int $isProcessed): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setIsProcessed($isProcessed);

        return $builder;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function withStoreId(int $storeId): SearchQueryBuilder
    {
        $builder = clone $this;
        $builder->query->setStoreId($storeId);

        return $builder;

    }

    /**
     * @return QueryInterface
     * @throws \Exception
     */
    public function build(): QueryInterface
    {
        try {
            $rule = $this->createQuery();
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }

        return $rule;
    }

    /**
     * @return QueryInterface
     * @throws AlreadyExistsException
     */
    private function createQuery(): QueryInterface
    {
        $builder = clone $this;
        $builder->queryResourceModel->save(object: $builder->query);

        return $builder->query;
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
