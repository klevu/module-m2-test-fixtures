<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class StoreGroupBuilder
{
    /**
     * @var GroupInterface
     */
    private GroupInterface $storeGroup;

    /**
     * @param GroupInterface $storeGroup
     */
    public function __construct(
        GroupInterface $storeGroup,
    ) {
        $this->storeGroup = $storeGroup;
    }

    /**
     * @return StoreGroupBuilder
     */
    public static function addStoreGroup(): StoreGroupBuilder //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(GroupInterface::class),
        );
    }

    /**
     * @param string $code
     *
     * @return StoreGroupBuilder
     */
    public function withCode(string $code): StoreGroupBuilder
    {
        $builder = clone $this;
        $builder->storeGroup->setCode($code);

        return $builder;
    }

    /**
     * @param string $name
     *
     * @return StoreGroupBuilder
     */
    public function withName(string $name): StoreGroupBuilder
    {
        $builder = clone $this;
        $builder->storeGroup->setName($name);

        return $builder;
    }

    /**
     * @param int $websiteId
     *
     * @return StoreGroupBuilder
     */
    public function withWebsiteId(int $websiteId): StoreGroupBuilder
    {
        $builder = clone $this;
        $builder->storeGroup->setWebsiteId($websiteId);

        return $builder;
    }

    /**
     * @param int $categoryId
     *
     * @return StoreGroupBuilder
     */
    public function withRootCategoryId(int $categoryId): StoreGroupBuilder
    {
        $builder = clone $this;
        $builder->storeGroup->setRootCategoryId($categoryId);

        return $builder;
    }

    /**
     * @return GroupInterface
     * @throws \Exception
     */
    public function build(): GroupInterface
    {
        try {
            $builder = $this->createStoreGroup();

            return $this->saveStoreGroup($builder);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return GroupInterface
     */
    public function buildWithoutSave(): GroupInterface
    {
        $builder = $this->createStoreGroup();

        return $builder->storeGroup;
    }

    /**
     * @return StoreGroupBuilder
     */
    private function createStoreGroup(): StoreGroupBuilder
    {
        $builder = clone $this;

        if (!$builder->storeGroup->getCode()) {
            $builder->storeGroup->setCode('klevu_test_store_group_1');
        }
        if (!$builder->storeGroup->getName()) {
            $builder->storeGroup->setName(
                ucwords(str_replace(['_', '-'], ' ', $builder->storeGroup->getCode())),
            );
        }
        if (null === $builder->storeGroup->getWebsiteId()) {
            $builder->storeGroup->setWebsiteId(1);
        }
        if (null === $builder->storeGroup->getRootCategoryId()) {
            $builder->storeGroup->setRootCategoryId(2);
        }

        return $builder;
    }

    /**
     * @param StoreGroupBuilder $builder
     *
     * @return GroupInterface
     * @throws AlreadyExistsException
     */
    private function saveStoreGroup(StoreGroupBuilder $builder): GroupInterface
    {
        // store group repository has no save methods so revert to resourceModel
        /** @var StoreGroupResourceModel $storeGroupResourceModel */
        $storeGroupResourceModel = $this->storeGroup->getResource();
        $storeGroupResourceModel->save($builder->storeGroup);

        return $builder->storeGroup;
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
