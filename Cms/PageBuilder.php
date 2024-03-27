<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Cms;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;

class PageBuilder
{
    /**
     * @var PageInterface
     */
    private PageInterface $page;
    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @param PageInterface $page
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        PageInterface $page,
        PageRepositoryInterface $pageRepository,
    ) {
        $this->page = $page;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return PageBuilder
     */
    public static function addPage(): PageBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(PageInterface::class),
            $objectManager->create(PageRepositoryInterface::class),
        );
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function withIdentifier(string $identifier): PageBuilder
    {
        $builder = clone $this;
        $builder->page->setIdentifier($identifier);

        return $builder;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function withTitle(string $title): PageBuilder
    {
        $builder = clone $this;
        $builder->page->setTitle($title);

        return $builder;
    }

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function withIsActive(bool $isActive): PageBuilder
    {
        $builder = clone $this;
        $builder->page->setIsActive($isActive);

        return $builder;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function withStoreId(int $storeId): PageBuilder
    {
        $builder = clone $this;
        /** @var Page $page */
        $page = $builder->page;
        $page->setStoreId($storeId);

        return $builder;
    }

    /**
     * @param int[] $stores
     *
     * @return $this
     */
    public function withStores(array $stores): PageBuilder
    {
        $builder = clone $this;
        $builder->page->setData('stores', $stores); // @phpstan-ignore-line

        return $builder;
    }

    /**
     * @return PageInterface
     * @throws LocalizedException
     */
    public function build(): PageInterface
    {
        try {
            $builder = $this->createPage();

            return $this->pageRepository->save($builder->page);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return PageBuilder
     */
    private function createPage(): PageBuilder
    {
        $builder = clone $this;

        if (!$builder->page->getIdentifier()) {
            $builder->page->setIdentifier('klevu-test-page');
        }
        if (!$builder->page->getTitle()) {
            $title = ucwords(
                str_replace('-', ' ', $builder->page->getIdentifier()),
            );
            $builder->page->setTitle($title);
        }
        if (!$builder->page->getContentHeading()) {
            $builder->page->setContentHeading('Heading - ' . $builder->page->getTitle());
        }
        if (!$builder->page->getContent()) {
            $builder->page->setContent('Content - ' . $builder->page->getTitle());
        }
        if (!$builder->page->getMetaDescription()) {
            $builder->page->setMetaDescription('Meta Description - ' . $builder->page->getTitle());
        }
        if (!$builder->page->getPageLayout()) {
            $builder->page->setPageLayout('1column');
        }
        if (null === $builder->page->isActive()) {
            $builder->page->setIsActive(true);
        }
        if (null === $builder->page->getStoreId()) {
            $builder->page->setStoreId(0);
        }
        if (null === $builder->page->getStores()) {
            $builder->page->setData('stores', [0]);
        }

        return $builder;
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
