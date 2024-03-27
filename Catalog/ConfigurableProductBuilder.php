<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as ConfigurableOptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as DownloadableLinkInterfaceFactory;
use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface as DownloadableLinkRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\ProductBuilder;

class ConfigurableProductBuilder extends ProductBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var IndexerFactory
     */
    private IndexerFactory $indexerFactory;
    /**
     * @var EavSetup|null
     */
    private ?EavSetup $eavSetup = null;
    /**
     * @var EavConfig|null
     */
    private ?EavConfig $eavConfig = null;
    /**
     * @var EavAttributeFactory|null
     */
    private ?EavAttributeFactory $eavAttributeFactory = null;
    /**
     * @var AttributeRepositoryInterface|null
     */
    private ?AttributeRepositoryInterface $attributeRepository = null;
    /**
     * @var ConfigurableOptionsFactory|null
     */
    private ?ConfigurableOptionsFactory $configurableOptionsFactory = null;
    /**
     * @var mixed[][]
     */
    private array $configurableAttributes = [];
    /**
     * @var ProductBuilder[]
     */
    private array $variants = [];

    /**
     * @return ConfigurableProductBuilder
     */
    public static function aConfigurableProduct(): ConfigurableProductBuilder // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Product $product */
        $product = $objectManager->create(Product::class);

        // Set some default values. These can be overridden afterwards using with... methods
        $product->setTypeId(Configurable::TYPE_CODE);
        $product->setAttributeSetId(4);
        $product->setName('Configurable Product');
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->addData([
            'tax_class_id' => 1,
            'description' => 'Description',
        ]);
        $product->setStockData([
            'manage_stock' => 0,
            'is_in_stock' => 1,
        ]);

        return self::getBuilderReturnObject(
            product: $product,
        );
    }

    /**
     * @param string $attributeCode
     * @param mixed[] $attributeData
     * @return ConfigurableProductBuilder
     */
    public function withConfigurableAttribute(
        string $attributeCode,
        array $attributeData = [],
    ): ConfigurableProductBuilder {
        $builder = clone $this;
        if (!array_key_exists($attributeCode, $builder->configurableAttributes)) {
            $builder->configurableAttributes[$attributeCode] = $attributeData;
        }

        return $builder;
    }

    /**
     * @param ProductBuilder $variantProductBuilder
     * @return $this
     */
    public function withVariant(ProductBuilder $variantProductBuilder): ConfigurableProductBuilder
    {
        $builder = clone $this;
        $builder->variants[] = $variantProductBuilder;

        return $builder;
    }

    /**
     * @return ProductInterface
     * @throws LocalizedException
     * @throws \Exception
     */
    public function build(): ProductInterface
    {
        $attributeOptionValues = $this->extractAttributeOptionValues(
            configurableAttributeCodes: array_keys($this->configurableAttributes),
            variantProductBuilders: $this->variants,
        );
        $configurableAttributes = $this->createConfigurableAttributes(
            configurableAttributesData: $this->configurableAttributes,
            attributeOptionValues: $attributeOptionValues,
        );
        $variantProducts = $this->createVariants(
            variantProductBuilders: $this->variants,
            configurableAttributes: $configurableAttributes,
        );

        $product = parent::build();

        $this->associateChildren(
            configurableProduct: $product,
            configurableAttributes: $configurableAttributes,
            variantProducts: $variantProducts,
        );

        $indexers = [
            'cataloginventory_stock',
            'catalog_product_price',
        ];
        foreach ($indexers as $indexerName) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerName);
            $indexer->reindexRow($product->getId());
        }

        return $product;
    }

    /**
     * @param string[] $configurableAttributeCodes
     * @param ProductBuilder[] $variantProductBuilders
     * @return string[]
     * @todo Abstract to separate service
     */
    private function extractAttributeOptionValues(
        array $configurableAttributeCodes,
        array $variantProductBuilders,
    ): array {
        $return = array_fill_keys(
            keys: $configurableAttributeCodes,
            value: [],
        );

        array_walk(
            $return,
            // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
            static function (array &$attributeValues, string $attributeCode) use ($variantProductBuilders): void {
                $attributeValues = array_map(
                    static fn (ProductBuilder $productBuilder): string => (
                    trim((string)$productBuilder->product->getData($attributeCode))
                    ),
                    $variantProductBuilders,
                );

                $attributeValues = array_unique(
                    array_filter(
                        $attributeValues,
                        static fn (string $value): bool => ('' !== $value),
                    ),
                );
            },
        );

        return $return;
    }

    /**
     * @param mixed[] $configurableAttributesData
     * @param string[] $attributeOptionValues
     * @return EavAttribute[]
     * @throws LocalizedException
     * @throws StateException
     * @todo Abstract to separate service
     */
    private function createConfigurableAttributes(
        array $configurableAttributesData,
        array $attributeOptionValues,
    ): array {
        $return = [];
        foreach ($configurableAttributesData as $attributeCode => $attributeData) {
            $configurableAttribute = $this->eavConfig?->getAttribute(
                entityType: ProductAttributeInterface::ENTITY_TYPE_CODE,
                code: $attributeCode,
            );
            if (!$configurableAttribute?->getId()) {
                $configurableAttribute = $this->createConfigurableAttribute(
                    attributeCode: $attributeCode,
                    attributeOptionValues: $attributeOptionValues[$attributeCode] ?? [],
                    attributeData: $attributeData,
                );
            }

            $this->eavSetup->addAttributeToGroup(
                entityType: ProductAttributeInterface::ENTITY_TYPE_CODE,
                setId: 'Default',
                groupId: 'General',
                attributeId: $configurableAttribute->getId(),
            );

            $return[$configurableAttribute->getAttributeCode()] = $configurableAttribute;
        }

        return $return;
    }

    /**
     * @param string $attributeCode
     * @param string[] $attributeOptionValues
     * @param mixed[] $attributeData
     * @return EavAttribute
     * @throws LocalizedException
     * @throws StateException
     * @todo Abstract to separate service
     */
    private function createConfigurableAttribute(
        string $attributeCode,
        array $attributeOptionValues,
        array $attributeData = [],
    ): EavAttribute {
        $productEntityTypeId = $this->eavSetup->getEntityTypeId(
            entityTypeId: ProductAttributeInterface::ENTITY_TYPE_CODE,
        );

        $optionKeys = array_map(
            static fn (string $optionValue): string => strtolower(str_replace(' ', '_', $optionValue)),
            $attributeOptionValues,
        );
        $options = $optionKeys
            ? [
                'value' => array_combine(
                    $optionKeys,
                    array_map(
                        static fn (string $optionValue): array => [$optionValue],
                        $attributeOptionValues,
                    ),
                ),
                'order' => array_combine(
                    $optionKeys,
                    range(1, count($optionKeys)),
                ),
            ]
            : [];

        $configurableAttribute = $this->eavAttributeFactory->create();
        $configurableAttribute->addData(
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
                    'frontend_label' => [$attributeCode],
                ],
                $attributeData,
                [
                    'attribute_code' => $attributeCode,
                    'entity_type_id' => $productEntityTypeId,
                    'is_global' => 1,
                    'is_user_defined' => 1,
                    'frontend_input' => 'select',
                    'backend_type' => 'int',
                    'option' => $options,
                ],
            ),
        );
        $this->attributeRepository->save($configurableAttribute);

        return $configurableAttribute;
    }

    /**
     * @param ProductBuilder[] $variantProductBuilders
     * @param EavAttribute[] $configurableAttributes
     * @return ProductInterface[]
     * @throws \Exception
     */
    private function createVariants(
        array $variantProductBuilders,
        array $configurableAttributes,
    ): array {
        return array_map(
            static function (ProductBuilder $variantProductBuilder) use ($configurableAttributes): ProductInterface {
                $variantProductFixture = $variantProductBuilder->product;

                foreach ($configurableAttributes as $attributeCode => $attribute) {
                    if (!$variantProductFixture->hasData($attributeCode)) {
                        continue;
                    }

                    $attributeOption = current(
                        array_filter(
                            $attribute->getOptions(),
                            static fn (AttributeOptionInterface $attributeOption): bool => (
                                $attributeOption->getLabel() === $variantProductFixture->getData($attributeCode)
                            ),
                        ),
                    );
                    $variantProductFixture->setData($attributeCode, $attributeOption?->getValue());
                }

                return $variantProductBuilder->build();
            },
            $variantProductBuilders,
        );
    }

    /**
     * @param ProductInterface $configurableProduct
     * @param EavAttribute[] $configurableAttributes
     * @param ProductInterface[] $variantProducts
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    private function associateChildren(
        ProductInterface $configurableProduct,
        array $configurableAttributes,
        array $variantProducts,
    ): void {
        if (!$configurableAttributes) {
            return;
        }

        $attributeValues = [];
        foreach ($configurableAttributes as $attributeCode => $configurableAttribute) {
            $attributeValues[$attributeCode] = [];

            /** @var Product $variantProduct */
            foreach ($variantProducts as $variantProduct) {
                $attributeValues[$configurableAttribute->getAttributeCode()][] = [
                    'label' => 'test',
                    'attribute_id' => $configurableAttribute->getId(),
                    'value_index' => $variantProduct->getData($configurableAttribute->getAttributeCode()),
                ];
            }
        }

        $configurableAttributesData = [];
        $position = 0;
        foreach ($attributeValues as $attributeCode => $values) {
            $configurableAttribute = $configurableAttributes[$attributeCode];

            $configurableAttributesData[] = [
                'attribute_id' => $configurableAttribute->getId(),
                'code' => $configurableAttribute->getAttributeCode(),
                'label' => $configurableAttribute->getDataUsingMethod('store_label'),
                'position' => $position++,
                'values' => $values,
            ];
        }

        $extensionConfigurableAttributes = $configurableProduct->getExtensionAttributes();

        $configurableOptions = $this->configurableOptionsFactory->create($configurableAttributesData);
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks(
            array_map(
                static fn (ProductInterface $variantProduct): int => (int)$variantProduct->getId(),
                $variantProducts,
            ),
        );

        $configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);

        $this->productRepository->save($configurableProduct);
    }

    /**
     * @param Product $product
     * @return ConfigurableProductBuilder
     */
    private static function getBuilderReturnObject( // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
        Product $product,
    ): ConfigurableProductBuilder {
        $objectManager = Bootstrap::getObjectManager();

        $productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $indexerFactory = $objectManager->create(IndexerFactory::class);

        $return = new static(
            productRepository: $productRepository,
            stockItemRepository: $objectManager->create(StockItemRepositoryInterface::class),
            websiteLinkRepository: $objectManager->create(ProductWebsiteLinkRepositoryInterface::class),
            websiteLinkFactory: $objectManager->create(ProductWebsiteLinkInterfaceFactory::class),
            indexerFactory: $indexerFactory,
            downloadLinkRepository: $objectManager->create(DownloadableLinkRepositoryInterface::class),
            downloadLinkFactory: $objectManager->create(DownloadableLinkInterfaceFactory::class),
            domainManager: $objectManager->create(DomainManagerInterface::class),
            product: $product,
            websiteIds: [1],
            storeSpecificValues: [],
        );

        // Private properties we also need
        $return->productRepository = $productRepository;
        $return->indexerFactory = $indexerFactory;

        // We can't extend the parent constructor, so these live here now
        $return->eavSetup = $objectManager->create(EavSetup::class);
        $return->eavConfig = $objectManager->create(EavConfig::class);
        $return->eavAttributeFactory = $objectManager->create(EavAttributeFactory::class);
        $return->attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);
        $return->configurableOptionsFactory = $objectManager->create(ConfigurableOptionsFactory::class);

        return $return;
    }
}
