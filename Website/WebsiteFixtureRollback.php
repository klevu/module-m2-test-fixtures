<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Website;

use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class WebsiteFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var WebsiteRepositoryInterface
     */
    private WebsiteRepositoryInterface $websiteRepository;

    /**
     * @param Registry $registry
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Registry $registry,
        WebsiteRepositoryInterface $websiteRepository,
    ) {
        $this->registry = $registry;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @return WebsiteFixtureRollback
     */
    public static function create(): WebsiteFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(WebsiteRepositoryInterface::class),
        );
    }

    /**
     * Roll back websites.
     *
     * @param WebsiteFixture ...$websiteFixtures
     *
     * @throws InvalidModelException
     * @throws \Exception
     */
    public function execute(WebsiteFixture ...$websiteFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($websiteFixtures as $websiteFixture) {
            try {
                /** @var WebsiteInterface|AbstractModel $website */
                $website = $this->websiteRepository->get((string)$websiteFixture->getId());
                if (!method_exists($website, 'getResource')) {
                    throw new InvalidModelException(
                        sprintf(
                            'Provided Model %s does not have require method %s.',
                            $website::class,
                            'getResource',
                        ),
                    );
                }
                // website repository has no delete methods so revert to resourceModel
                $websiteResourceModel = $website->getResource();
                if (!($websiteResourceModel instanceof WebsiteResourceModel)) {
                    throw new InvalidModelException(
                        sprintf(
                            'Resource Model %s is not an instance of %s.',
                            $websiteResourceModel::class,
                            WebsiteResourceModel::class,
                        ),
                    );
                }
                $websiteResourceModel->delete($website);
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // website has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
