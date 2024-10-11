<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\Attribute\AttributeBuilder;
use Klevu\TestFixtures\Catalog\Attribute\AttributeFixturePool;
use Klevu\TestFixtures\Traits\AttributeApiCallTrait;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

trait AttributeTrait
{
    use AttributeApiCallTrait;

    /**
     * @var AttributeFixturePool|null
     */
    private ?AttributeFixturePool $attributeFixturePool = null;

    /**
     * @param mixed[] $attributeData
     *
     * @return void
     * @throws \Exception
     */
    public function createAttribute(?array $attributeData = []): void
    {
        if (!($attributeData['trigger_real_api'] ?? null)) {
            $this->mockSdkAttributeGetApiCall();
        }

        if (!($attributeData['attribute_type'] ?? null)) {
            $attributeData['attribute_type'] = 'text';
        }
        if (!($attributeData['code'] ?? null)) {
            $attributeData['code'] = 'klevu_test_config_attribute';
        }

        if (($attributeData['entity_type'] ?? null) === CategoryAttributeInterface::ENTITY_TYPE_CODE) {
            $attributeBuilder = AttributeBuilder::aCategoryAttribute(
                attributeCode: $attributeData['code'],
                attributeType: $attributeData['attribute_type'],
                attributeData: $attributeData,
            );
        } else {
            $attributeBuilder = match ($attributeData['attribute_type'] ?? null) {
                'configurable' => AttributeBuilder::aConfigurableAttribute($attributeData['code']),
                default => AttributeBuilder::aProductAttribute(
                    attributeCode: $attributeData['code'],
                    attributeType: $attributeData['attribute_type'],
                    attributeData: $attributeData,
                ),
            };
        }

        if (!($attributeData['label'] ?? null)) {
            $attributeData['label'] = ucwords(str_replace('_', ' ', $attributeData['code']));
        }
        $attributeBuilder = $attributeBuilder->withLabel($attributeData['label']);

        if ($attributeData['labels'] ?? null) {
            $attributeBuilder = $attributeBuilder->withLabels($attributeData['labels']);
        }

        if ($attributeData['data'] ?? null) {
            $attributeBuilder = $attributeBuilder->withAttributeData($attributeData['data']);
        }

        if (
            !($attributeData['options'] ?? null)
            && (
                in_array(($attributeData['attribute_type'] ?? null), ['configurable', 'select', 'multiselect'], true)
                || in_array(($attributeData['data']['frontend_input'] ?? null), ['select', 'multiselect'], true)
            )
        ) {
            $attributeData['options'] = [
                '1' => 'Option 1',
                '2' => 'Option 2',
                '3' => 'Option 3',
                '4' => 'Option 4',
                '5' => 'Option 5',
            ];
        }
        if ($attributeData['options'] ?? null) {
            $attributeBuilder = $attributeBuilder->withOptions($attributeData['options']);
        }

        if ($attributeData['index_as'] ?? null) {
            $attributeBuilder = $attributeBuilder->withIndexAs($attributeData['index_as']);
        }

        if ($attributeData['aspect'] ?? null) {
            $attributeBuilder = $attributeBuilder->withAspect($attributeData['aspect']);
        }

        if ($attributeData['generate_config_for'] ?? null) {
            $attributeBuilder = $attributeBuilder->withGenerateConfigFor($attributeData['generate_config_for']);
        }

        $attributeBuilder = $attributeBuilder->withEntityType(
            $attributeData['entity_type'] ?? ProductAttributeInterface::ENTITY_TYPE_CODE,
        );

        $this->attributeFixturePool->add(
            attribute: $attributeBuilder->build(),
            key: $attributeData['key'] ?? 'test_attribute',
        );
    }
}
