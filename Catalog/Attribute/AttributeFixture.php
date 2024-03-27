<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;

class AttributeFixture
{
    /**
     * @var AttributeInterface
     */
    private AttributeInterface $attribute;

    /**
     * @param AttributeInterface $attribute
     */
    public function __construct(AttributeInterface $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @return int
     */
    public function getAttributeId(): int
    {
        return (int)$this->attribute->getAttributeId();
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->attribute->getAttributeCode();
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        AttributeFixtureRollback::create()->execute($this);
    }
}
