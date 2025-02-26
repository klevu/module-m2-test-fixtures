<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\SalesSequence\Model\EntityPool as SalesSequenceEntityPool;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class StoreBuilder
{
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;
    /**
     * @var DdlSequence
     */
    private DdlSequence $ddlSequence;
    /**
     * @var SalesSequenceEntityPool
     */
    private SalesSequenceEntityPool $salesSequenceEntityPool;
    /**
     * @var bool
     */
    private bool $withSequence = false;

    /**
     * @param StoreInterface $store
     * @param ResourceConnection $resourceConnection
     * @param DdlSequence $ddlSequence
     * @param SalesSequenceEntityPool $salesSequenceEntityPool
     */
    public function __construct(
        StoreInterface $store,
        ResourceConnection $resourceConnection,
        DdlSequence $ddlSequence,
        SalesSequenceEntityPool $salesSequenceEntityPool,
    ) {
        $this->store = $store;
        $this->resourceConnection = $resourceConnection;
        $this->ddlSequence = $ddlSequence;
        $this->salesSequenceEntityPool = $salesSequenceEntityPool;
    }

    /**
     * @return StoreBuilder
     */
    public static function addStore(): StoreBuilder //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(StoreInterface::class),
            $objectManager->create(ResourceConnection::class),
            $objectManager->create(DdlSequence::class),
            $objectManager->get(SalesSequenceEntityPool::class),
        );
    }

    /**
     * @param string $code
     *
     * @return StoreBuilder
     */
    public function withCode(string $code): StoreBuilder
    {
        $builder = clone $this;
        $builder->store->setCode($code);

        return $builder;
    }

    /**
     * @param string $name
     *
     * @return StoreBuilder
     */
    public function withName(string $name): StoreBuilder
    {
        $builder = clone $this;
        $builder->store->setName($name);

        return $builder;
    }

    /**
     * @param int $websiteId
     *
     * @return StoreBuilder
     */
    public function withWebsiteId(int $websiteId): StoreBuilder
    {
        $builder = clone $this;
        $builder->store->setWebsiteId($websiteId);

        return $builder;
    }

    /**
     * @param int $groupId
     *
     * @return StoreBuilder
     */
    public function withGroupId(int $groupId): StoreBuilder
    {
        $builder = clone $this;
        $builder->store->setStoreGroupId($groupId);

        return $builder;
    }

    /**
     * @param bool $isActive
     *
     * @return StoreBuilder
     */
    public function withIsActive(bool $isActive): StoreBuilder
    {
        $builder = clone $this;
        $builder->store->setIsActive($isActive);

        return $builder;
    }

    /**
     * @param bool $withSequence
     *
     * @return void
     */
    public function withSequence(bool $withSequence = false): void
    {
        $this->withSequence = $withSequence;
    }

    /**
     * @return StoreInterface
     * @throws \Exception
     */
    public function build(): StoreInterface
    {
        try {
            $builder = $this->createStore();

            return $this->saveStore($builder);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return StoreInterface
     */
    public function buildWithoutSave(): StoreInterface
    {
        $builder = $this->createStore();

        return $builder->store;
    }

    /**
     * @return StoreBuilder
     */
    private function createStore(): StoreBuilder
    {
        $builder = clone $this;

        if (!$builder->store->getCode()) {
            $builder->store->setCode('klevu_test_store_1');
        }
        if (!$builder->store->getName()) {
            $builder->store->setName(
                ucwords(str_replace(['_', '-'], ' ', $builder->store->getCode())),
            );
        }
        if (null === $builder->store->getWebsiteId()) {
            $builder->store->setWebsiteId(1);
        }
        if (null === $builder->store->getStoreGroupId()) {
            $builder->store->setStoreGroupId(1);
        }
        if (null === $builder->store->getIsActive()) {
            $builder->store->setIsActive(true);
        }

        return $builder;
    }

    /**
     * @param StoreBuilder $builder
     *
     * @return StoreInterface
     * @throws AlreadyExistsException
     */
    private function saveStore(StoreBuilder $builder): StoreInterface
    {
        // store repository has no save methods so revert to resourceModel
        /** @var StoreResourceModel $storeResourceModel */
        $storeResourceModel = $this->store->getResource();
        $storeResourceModel->save($builder->store);
        if ($this->withSequence) {
            $this->createSequenceTables($builder->store);
        }

        return $builder->store;
    }

    /**
     * @param StoreInterface $store
     *
     * @return void
     */
    private function createSequenceTables(StoreInterface $store): void
    {
        $connection = $this->resourceConnection->getConnection(resourceName: 'sales');
        foreach ($this->salesSequenceEntityPool->getEntities() as $entityType) {
            $sequenceTableName = $this->resourceConnection->getTableName(
                modelEntity: sprintf(
                    'sequence_%s_%s',
                    $entityType,
                    $store->getId(),
                ),
            );

            if (!$connection->isTableExists($sequenceTableName)) {
                $connection->query(
                    sql: $this->ddlSequence->getCreateSequenceDdl(
                        name: $sequenceTableName,
                    ),
                );
                $connection->insertOnDuplicate(
                    table: $this->resourceConnection->getTableName(
                        modelEntity: 'sales_sequence_meta',
                    ),
                    data: [
                        'entity_type' => $entityType,
                        'store_id' => $store->getId(),
                        'sequence_table' => $sequenceTableName,
                    ],
                    fields: [],
                );
                $select = $connection->select()
                    ->from(
                        name: $this->resourceConnection->getTableName(
                            modelEntity: 'sales_sequence_meta',
                        ),
                        cols: ['meta_id'],
                    )->where(
                        cond: 'store_id = ?',
                        value: $store->getId(),
                    )->where(
                        cond: 'sequence_table = ?',
                        value: $sequenceTableName,
                    );
                $result = $connection->fetchRow($select);

                $connection->insertOnDuplicate(
                    table: $this->resourceConnection->getTableName(
                        modelEntity: 'sales_sequence_profile',
                    ),
                    data: [
                        'meta_id' => $result['meta_id'],
                        'is_active' => 1,
                    ],
                    fields: [],
                );
            }
        }
    }

    /**
     * @param \Throwable|null $exception
     *
     * @return bool
     *
     */
    private static function isTransactionException( // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
        ?\Throwable $exception,
    ): bool {
        if ($exception === null) {
            return false;
        }

        return (bool)preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
