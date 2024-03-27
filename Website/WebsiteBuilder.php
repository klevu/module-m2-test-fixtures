<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Website;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class WebsiteBuilder
{
    /**
     * @var WebsiteInterface
     */
    private WebsiteInterface $website;

    /**
     * @param WebsiteInterface $website
     */
    public function __construct(
        WebsiteInterface $website,
    ) {
        $this->website = $website;
    }

    /**
     * @return WebsiteBuilder
     */
    public static function addWebsite(): WebsiteBuilder //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(WebsiteInterface::class),
        );
    }

    /**
     * @param string $code
     *
     * @return WebsiteBuilder
     */
    public function withCode(string $code): WebsiteBuilder
    {
        $builder = clone $this;
        $builder->website->setCode($code);

        return $builder;
    }

    /**
     * @param string $name
     *
     * @return WebsiteBuilder
     */
    public function withName(string $name): WebsiteBuilder
    {
        $builder = clone $this;
        $builder->website->setName($name);

        return $builder;
    }

    /**
     * @param int $groupId
     *
     * @return WebsiteBuilder
     */
    public function withDefaultGroupId(int $groupId): WebsiteBuilder
    {
        $builder = clone $this;
        $builder->website->setDefaultGroupId($groupId);

        return $builder;
    }

    /**
     * @return WebsiteInterface
     * @throws \Exception
     */
    public function build(): WebsiteInterface
    {
        try {
            $builder = $this->createWebsite();

            return $this->saveWebsite($builder);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return WebsiteInterface
     */
    public function buildWithoutSave(): WebsiteInterface
    {
        $builder = $this->createWebsite();

        return $builder->website;
    }

    /**
     * @return WebsiteBuilder
     */
    private function createWebsite(): WebsiteBuilder
    {
        $builder = clone $this;

        if (!$builder->website->getCode()) {
            $builder->website->setCode('klevu_test_website_1');
        }
        if (!$builder->website->getName()) {
            $builder->website->setName(
                ucwords(str_replace(['_', '-'], ' ', $builder->website->getCode())),
            );
        }
        if (null === $builder->website->getDefaultGroupId()) {
            $builder->website->setDefaultGroupId(1);
        }

        return $builder;
    }

    /**
     * @param WebsiteBuilder $builder
     *
     * @return WebsiteInterface
     * @throws AlreadyExistsException
     */
    private function saveWebsite(WebsiteBuilder $builder): WebsiteInterface
    {
        // website repository has no save methods so revert to resourceModel
        /** @var WebsiteResourceModel $websiteResourceModel */
        $websiteResourceModel = $this->website->getResource();
        $websiteResourceModel->save($builder->website);

        return $builder->website;
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
