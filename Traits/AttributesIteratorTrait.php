<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\PhpSDK\Api\Model\Indexing\AttributeInterface;
use Klevu\PhpSDK\Model\Indexing\Attribute;
use Klevu\PhpSDK\Model\Indexing\AttributeIterator;
use Klevu\PhpSDK\Model\Indexing\DataType;
use Magento\Framework\ObjectManagerInterface;

trait AttributesIteratorTrait
{
    /**
     * @param AttributeInterface[] $attributes
     * @param bool $includeStandardAttributes
     *
     * @return AttributeIterator
     */
    private function createAttributeIterator(
        array $attributes = [],
        bool $includeStandardAttributes = true,
    ): AttributeIterator {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        $data = $includeStandardAttributes
            ? $this->getStandardAttributes()
            : [];
        foreach ($attributes as $key => $attribute) {
            $data[$key] = $attribute;
        }

        return $this->objectManager->create(
            type: AttributeIterator::class,
            arguments: [
                'data' => $data,
            ],
        );
    }

    /**
     * @return AttributeInterface[]
     */
    private function getStandardAttributes(): array
    {
        $data = [];
        $data['categoryPath'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'categoryPath',
                'datatype' => DataType::STRING->value,
                'searchable' => false,
                'filterable' => true,
                'returnable' => false,
                'immutable' => true,
            ],
        );
        $data['createdAt'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'createdAt',
                'datatype' => DataType::STRING->value,
                'searchable' => false,
                'filterable' => true,
                'returnable' => false,
                'immutable' => true,
            ],
        );
        $data['description'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'description',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['image'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'image',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['inStock'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'inStock',
                'datatype' => DataType::BOOLEAN->value,
                'searchable' => false,
                'filterable' => true,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['name'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'name',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['price'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'price',
                'datatype' => DataType::NUMBER->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['rating'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'rating',
                'datatype' => DataType::NUMBER->value,
                'searchable' => true,
                'filterable' => true,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['ratingCount'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'ratingCount',
                'datatype' => DataType::NUMBER->value,
                'searchable' => true,
                'filterable' => true,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['shortDescription'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'shortDescription',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['sku'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'sku',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['tags'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'tags',
                'datatype' => DataType::MULTIVALUE->value,
                'searchable' => false,
                'filterable' => true,
                'returnable' => false,
                'immutable' => true,
            ],
        );
        $data['url'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'url',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );
        $data['visibility'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'visibility',
                'datatype' => DataType::MULTIVALUE->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'immutable' => true,
            ],
        );

        return $data;
    }
}
