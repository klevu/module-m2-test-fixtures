<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Cms;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class PageFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @param Registry $registry
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(Registry $registry, PageRepositoryInterface $pageRepository)
    {
        $this->registry = $registry;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return PageFixtureRollback
     */
    public static function create(): PageFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(PageRepositoryInterface::class),
        );
    }

    /**
     * @param PageFixture ...$pageFixtures
     *
     * @return void
     */
    public function execute(PageFixture ...$pageFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($pageFixtures as $pageFixture) {
            try {
                $this->pageRepository->deleteById($pageFixture->getId());
            } catch (LocalizedException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // store has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
