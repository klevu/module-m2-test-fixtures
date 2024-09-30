<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Attribute;

// phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder

use Klevu\IndexingApi\Model\MagentoAttributeInterface;
use Klevu\IndexingApi\Model\Source\IndexType;
use Klevu\IndexingCategories\Model\Source\Aspect as CategoryAspect;
use Klevu\IndexingProducts\Model\Source\Aspect as ProductAspect;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\OptionManagement;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax as BackendWeeTax;
use TddWizard\Fixtures\Catalog\IndexFailed;

class AttributeBuilder
{
    /**
     * @var EavConfig
     */
    private EavConfig $eavConfig;
    /**
     * @var EavSetup
     */
    private EavSetup $eavSetup;
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;
    /**
     * @var AttributeOptionInterfaceFactory
     */
    private AttributeOptionInterfaceFactory $attributeOptionFactory;
    /**
     * @var Attribute
     */
    private Attribute $attribute;
    /**
     * @var string
     */
    private string $attributeCode;
    /**
     * @var string
     */
    private string $attributeType;

    /**
     * @param EavConfig $eavConfig
     * @param EavSetup $eavSetup
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionInterfaceFactory $attributeOptionFactory
     * @param Attribute $attribute
     * @param string $attributeCode
     * @param string $attributeType
     */
    public function __construct(
        EavConfig $eavConfig,
        EavSetup $eavSetup,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionInterfaceFactory $attributeOptionFactory,
        Attribute $attribute,
        string $attributeCode = '',
        string $attributeType = '',
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetup = $eavSetup;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attribute = $attribute;
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->attribute = clone $this->attribute;
    }

    /**
     * @param string $attributeCode
     *
     * @return AttributeBuilder
     */
    public static function aConfigurableAttribute(string $attributeCode): AttributeBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Attribute $attribute */
        $attribute = $objectManager->create(ProductAttributeInterface::class);

        $attribute->addData(
            array_merge(
                [
                    'is_unique' => 0,
                    'is_required' => 0,
                    'is_searchable' => 0,
                    'is_visible_in_advanced_search' => 0,
                    'is_comparable' => 0,
                    'is_filterable' => 0,
                    'is_filterable_in_search' => 0,
                    'is_used_for_promo_rules' => 0,
                    'is_html_allowed_on_front' => 0,
                    'is_visible_on_front' => 0,
                    'used_in_product_listing' => 0,
                    'used_for_sort_by' => 0,
                ],
            ),
        );

        return new static(
            $objectManager->create(EavConfig::class),
            $objectManager->create(EavSetup::class),
            $objectManager->create(AttributeRepositoryInterface::class),
            $objectManager->create(AttributeOptionInterfaceFactory::class),
            $attribute,
            $attributeCode,
            'configurable',
        );
    }

    /**
     * @param string $attributeCode
     * @param string|null $attributeType
     * @param mixed[] $attributeData
     *
     * @return AttributeBuilder
     */
    public static function aProductAttribute(
        string $attributeCode,
        ?string $attributeType = null,
        array $attributeData = [],
    ): AttributeBuilder {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Attribute $attribute */
        $attribute = $objectManager->create(ProductAttributeInterface::class);

        $defaultAttributeData = [
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 0,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'is_global' => 0,
        ];
        $attributeData = array_intersect_key($attributeData, $defaultAttributeData);

        $attribute->addData(
            array_merge(
                $defaultAttributeData,
                $attributeData,
            ),
        );

        return new static(
            $objectManager->create(EavConfig::class),
            $objectManager->create(EavSetup::class),
            $objectManager->create(AttributeRepositoryInterface::class),
            $objectManager->create(AttributeOptionInterfaceFactory::class),
            $attribute,
            $attributeCode,
            $attributeType,
        );
    }

    /**
     * @param string $attributeCode
     * @param string|null $attributeType
     * @param mixed[] $attributeData
     *
     * @return AttributeBuilder
     */
    public static function aCategoryAttribute(
        string $attributeCode,
        ?string $attributeType = null,
        array $attributeData = [],
    ): AttributeBuilder {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Attribute $attribute */
        $attribute = $objectManager->create(CategoryAttributeInterface::class);

        $defaultAttributeData = [
            'is_unique' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 0,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'is_global' => 0,
        ];
        $attributeData = array_intersect_key($attributeData, $defaultAttributeData);

        $attribute->addData(
            array_merge(
                $defaultAttributeData,
                $attributeData,
            ),
        );

        return new static(
            $objectManager->create(EavConfig::class),
            $objectManager->create(EavSetup::class),
            $objectManager->create(AttributeRepositoryInterface::class),
            $objectManager->create(AttributeOptionInterfaceFactory::class),
            $attribute,
            $attributeCode,
            $attributeType,
        );
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function withLabel(string $label): AttributeBuilder
    {
        $builder = clone $this;
        $builder->attribute->setDefaultFrontendLabel($label);

        return $builder;
    }

    /**
     * @param mixed $labels
     *
     * @return $this
     */
    public function withLabels(mixed $labels): AttributeBuilder
    {
        $builder = clone $this;

        $objectManager = Bootstrap::getObjectManager();
        $labelFactory = $objectManager->get(AttributeFrontendLabelInterfaceFactory::class);
        $labelsToSave = [];
        foreach ($labels as $storeId => $label) {
            /** @var AttributeFrontendLabelInterface $labelToSave */
            $labelToSave = $labelFactory->create();
            $labelToSave->setStoreId($storeId);
            $labelToSave->setLabel($label);
            $labelsToSave[] = $labelToSave;
        }
        if ($labelsToSave) {
            $builder->attribute->setFrontendLabels($labelsToSave);
        }

        return $builder;
    }

    /**
     * @param mixed[] $attributeData
     *
     * @return $this
     */
    public function withAttributeData(array $attributeData): AttributeBuilder
    {
        $builder = clone $this;
        $data = $builder->attribute->getData();
        $builder->attribute->setData(
            array_merge($data, $attributeData),
        );

        return $builder;
    }

    /**
     * @param mixed[] $attributeOptionValues
     *
     * @return $this
     */
    public function withOptions(array $attributeOptionValues): AttributeBuilder
    {
        $builder = clone $this;

        $options = [];
        foreach ($attributeOptionValues as $value => $label) {
            /** @var AttributeOptionInterface $option */
            $option = $this->attributeOptionFactory->create();
            $option->setValue($value);
            $option->setLabel($label);
            $options[] = $option;
        }
        $builder->attribute = $builder->attribute->setOptions($options);

        return $builder;
    }

    /**
     * @param IndexType $indexType
     *
     * @return $this
     */
    public function withIndexAs(IndexType $indexType): AttributeBuilder
    {
        $builder = clone $this;
        $builder->attribute = $builder->attribute->setData(
            MagentoAttributeInterface::ATTRIBUTE_PROPERTY_IS_INDEXABLE,
            $indexType->value,
        );

        return $builder;
    }

    /**
     * @param CategoryAspect|ProductAspect $aspect
     *
     * @return $this
     */
    public function withAspect(CategoryAspect|ProductAspect $aspect): AttributeBuilder
    {
        $builder = clone $this;
        $builder->attribute = $builder->attribute->setData(
            MagentoAttributeInterface::ATTRIBUTE_PROPERTY_ASPECT_MAPPING,
            $aspect->value,
        );

        return $builder;
    }

    /**
     * @param string $entityType
     *
     * @return $this
     */
    public function withEntityType(string $entityType): AttributeBuilder
    {
        $builder = clone $this;
        $builder->attribute = $builder->attribute->setData('entity_type', $entityType);

        return $builder;
    }

    /**
     * @param string[] $entitySubtypes
     *
     * @return AttributeBuilder
     */
    public function withGenerateConfigFor(array $entitySubtypes): AttributeBuilder
    {
        $builder = clone $this;
        $builder->attribute = $builder->attribute->setData(
            MagentoAttributeInterface::ATTRIBUTE_PROPERTY_GENERATE_CONFIGURATION_FOR_ENTITY_SUBTYPES,
            $entitySubtypes,
        );

        return $builder;
    }

    /**
     * @return AttributeInterface
     * @throws \Exception
     */
    public function build(): AttributeInterface
    {
        try {
            $attribute = $this->createAttribute();
            if ($attribute instanceof ProductAttributeInterface) {
                $this->eavSetup->addAttributeToGroup(
                    entityType: ProductAttributeInterface::ENTITY_TYPE_CODE,
                    setId: 'Default',
                    groupId: 'General',
                    attributeId: $attribute->getId(),
                );
                if ($attribute->getOptions()) {
                    $objectManager = Bootstrap::getObjectManager();
                    $optionManagement = $objectManager->create(OptionManagement::class);
                    foreach ($attribute->getOptions() as $option) {
                        $optionManagement->add(
                            attributeCode: $attribute->getAttributeCode(),
                            option: $option,
                        );
                    }
                }
            }

            return $attribute;
        } catch (\Exception $e) {
            $e->getPrevious();
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return AttributeInterface
     * @throws LocalizedException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    private function createAttribute(): AttributeInterface
    {
        $builder = clone $this;

        $entityType = $builder->attribute->getData('entity_type');

        $attribute = $this->eavConfig->getAttribute(
            entityType: $entityType,
            code: $builder->attribute->getAttributeCode(),
        );
        if ($attribute?->getId()) {
            return $attribute;
        }

        $categoryEntityTypeId = $this->eavSetup->getEntityTypeId(
            entityTypeId: $entityType,
        );
        $builder->attribute->setAttributeCode($builder->attributeCode);
        $builder->attribute->setEntityTypeId($categoryEntityTypeId);
        $builder->attribute->setIsUserDefined(1);
        switch ($builder->attributeType) {
            case ('configurable'):
                $builder->attribute->setData('is_global', 1);
                $builder->attribute->setFrontendInput('select');
                $builder->attribute->setBackendType('int');
                break;
            case ('textarea'):
                $builder->attribute->setFrontendInput('textarea');
                $builder->attribute->setBackendType('text');
                break;
            case ('text'):
                $builder->attribute->setFrontendInput('text');
                $builder->attribute->setBackendType('varchar');
                break;
            case ('date'):
                $builder->attribute->setFrontendInput('date');
                $builder->attribute->setBackendType('datetime');
                break;
            case ('enum'):
                $builder->attribute->setFrontendInput('select');
                $builder->attribute->setBackendType('int');
                break;
            case ('select'):
                $builder->attribute->setFrontendInput('select');
                $builder->attribute->setBackendType('varchar');
                break;
            case ('boolean'):
                $builder->attribute->setFrontendInput('boolean');
                $builder->attribute->setBackendType('int');
                break;
            case ('multiselect'):
                $builder->attribute->setFrontendInput('multiselect');
                $builder->attribute->setBackendType('text');
                break;
            case ('price'):
                $builder->attribute->setFrontendInput('price');
                $builder->attribute->setBackendType('decimal');
                break;
            case ('image'):
                $builder->attribute->setFrontendInput('media_image');
                $builder->attribute->setBackendType('varchar');
                break;
            case ('weee'):
                $builder->attribute->setFrontendInput('weee');
                $builder->attribute->setBackendType('static');
                $builder->attribute->setBackendModel(BackendWeeTax::class);
                break;
            default:
                // no type provided, values can be set via 'withAttributeData'
                // where required combination is not listed above
                break;
        }

        try {
            $attributeCode = $builder->attributeRepository->save($builder->attribute);

            return $builder->attributeRepository->get(
                entityTypeCode: ProductAttributeInterface::ENTITY_TYPE_CODE,
                attributeCode: $attributeCode,
            );
        } catch (\Exception $e) {
            $e->getPrevious();
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @param \Throwable|null $exception
     * @return bool
     */
    private static function isTransactionException(?\Throwable $exception): bool
    {
        if ($exception === null) {
            return false;
        }
        return (bool) preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
