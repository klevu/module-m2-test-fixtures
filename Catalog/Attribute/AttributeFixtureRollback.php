<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Catalog\Attribute;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class AttributeFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param Registry $registry
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Registry $registry,
        AttributeRepositoryInterface $attributeRepository,
    ) {
        $this->registry = $registry;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return AttributeFixtureRollback
     */
    public static function create(
    ): AttributeFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(AttributeRepositoryInterface::class),
        );
    }

    /**
     * Roll back attributes.
     *
     * @param AttributeFixture ...$attributeFixtures
     */
    public function execute(AttributeFixture ...$attributeFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($attributeFixtures as $attributeFixture) {
            try {
                $this->attributeRepository->deleteById(
                    $attributeFixture->getAttributeId(),
                );
            } catch (\Exception) {
                // this is fine, attribute has already been removedZ
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
