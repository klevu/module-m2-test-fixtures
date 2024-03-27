<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;

class AttributeFixturePool
{
    /**
     * @var AttributeFixture[]
     */
    private array $attributeFixtures = [];

    /**
     * @param AttributeInterface $attribute
     * @param string|null $key
     *
     * @return void
     */
    public function add(AttributeInterface $attribute, ?string $key = null): void
    {
        if ($key === null) {
            $this->attributeFixtures[] = new AttributeFixture($attribute);
        } else {
            $this->attributeFixtures[$key] = new AttributeFixture($attribute);
        }
    }

    /**
     * Returns store fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return AttributeFixture
     */
    public function get(?string $key = null): AttributeFixture
    {
        if ($key === null) {
            $key = array_key_last($this->attributeFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->attributeFixtures)) {
            throw new \OutOfBoundsException('No matching attribute found in fixture pool');
        }

        return $this->attributeFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        AttributeFixtureRollback::create()->execute(...array_values($this->attributeFixtures));
        $this->attributeFixtures = [];
    }
}
