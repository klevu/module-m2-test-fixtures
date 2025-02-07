<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Category;

// phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder

use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ImageUploader;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir as Directory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\CategoryFixture;

/**
 * TddWizard\Fixtures\Catalog\CategoryBuilder has no way to set store id for categories
 * Would have extended but \TddWizard\Fixtures\Catalog\CategoryBuilder::$category is private
 */
class CategoryBuilder
{
    /**
     * @var CategoryResource
     */
    private readonly CategoryResource $categoryResource;
    /**
     * @var CategoryLinkRepositoryInterface
     */
    private readonly CategoryLinkRepositoryInterface $categoryLinkRepository;
    /**
     * @var CategoryProductLinkInterfaceFactory
     */
    private readonly CategoryProductLinkInterfaceFactory $productLinkFactory;
    /**
     * @var CategoryInterface
     */
    private CategoryInterface $category;
    /**
     * @var string[]
     */
    private array $skus;
    /**
     * @var mixed[][]
     */
    private array $storeSpecificValues;

    /**
     * @param CategoryResource $categoryResource
     * @param CategoryLinkRepositoryInterface $categoryLinkRepository
     * @param CategoryProductLinkInterfaceFactory $productLinkFactory
     * @param CategoryInterface $category
     * @param string[] $skus
     * @param mixed[] $storeSpecificValues
     */
    public function __construct(
        CategoryResource $categoryResource,
        CategoryLinkRepositoryInterface $categoryLinkRepository,
        CategoryProductLinkInterfaceFactory $productLinkFactory,
        CategoryInterface $category,
        array $skus,
        array $storeSpecificValues,
    ) {
        $this->categoryResource = $categoryResource;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->productLinkFactory = $productLinkFactory;
        $this->category = $category;
        $this->skus = $skus;
        $this->storeSpecificValues = $storeSpecificValues;
    }

    /**
     * @return CategoryBuilder
     */
    public static function rootCategory(): CategoryBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        // use interface to reflect DI configuration but assume instance of the real model because we need its methods
        /** @var Category $category */
        $category = $objectManager->create(CategoryInterface::class);

        $category->setName('Root Category');
        $category->setIsActive(true);
        $category->setPath('1');
        $category->setParentId(1);

        return new self(
            $objectManager->create(CategoryResource::class),
            $objectManager->create(CategoryLinkRepositoryInterface::class),
            $objectManager->create(CategoryProductLinkInterfaceFactory::class),
            $category,
            [],
            [],
        );
    }

    /**
     * @param int|null $rootCategoryId
     *
     * @return CategoryBuilder
     */
    public static function topLevelCategory(?int $rootCategoryId = null): CategoryBuilder
    {
        $rootCategoryId = $rootCategoryId ?? 2;
        $objectManager = Bootstrap::getObjectManager();

        // use interface to reflect DI configuration but assume instance of the real model because we need its methods
        /** @var Category $category */
        $category = $objectManager->create(CategoryInterface::class);

        $category->setName('Top Level Category');
        $category->setIsActive(true);
        $category->setPath('1/' . $rootCategoryId);
        $category->setParentId($rootCategoryId);

        return new self(
            $objectManager->create(CategoryResource::class),
            $objectManager->create(CategoryLinkRepositoryInterface::class),
            $objectManager->create(CategoryProductLinkInterfaceFactory::class),
            $category,
            [],
            [],
        );
    }

    /**
     * @param CategoryFixture $parent
     *
     * @return CategoryBuilder
     */
    public static function childCategoryOf(
        CategoryFixture $parent,
    ): CategoryBuilder {
        $objectManager = Bootstrap::getObjectManager();
        // use interface to reflect DI configuration but assume instance of the real model because we need its methods
        /** @var CategoryInterface $category */
        $category = $objectManager->create(CategoryInterface::class);

        $category->setName('Child Category');
        $category->setIsActive(true);
        $category->setPath((string)$parent->getCategory()->getPath());
        $category->setParentId((int)$parent->getCategory()->getId());

        return new self(
            $objectManager->create(CategoryResource::class),
            $objectManager->create(CategoryLinkRepositoryInterface::class),
            $objectManager->create(CategoryProductLinkInterfaceFactory::class),
            $category,
            [],
            [],
        );
    }

    /**
     * Assigns products by sku. The keys of the array will be used for the sort position
     *
     * @param string[] $skus
     *
     * @return CategoryBuilder
     */
    public function withProducts(array $skus): CategoryBuilder
    {
        $builder = clone $this;
        $builder->skus = $skus;

        return $builder;
    }

    /**
     * @param string $description
     * @param int|null $storeId
     *
     * @return CategoryBuilder
     */
    public function withDescription(string $description, ?int $storeId = null): CategoryBuilder
    {
        $builder = clone $this;
        if ($storeId) {
            $builder->storeSpecificValues[$storeId]['description'] = $description;
        } else {
            $builder->category->setCustomAttribute('description', $description);
        }

        return $builder;
    }

    /**
     * @param string $name
     * @param int|null $storeId
     *
     * @return CategoryBuilder
     */
    public function withName(string $name, ?int $storeId = null): CategoryBuilder
    {
        $builder = clone $this;
        if ($storeId) {
            $builder->storeSpecificValues[$storeId][CategoryInterface::KEY_NAME] = $name;
        } else {
            $builder->category->setName($name);
        }

        return $builder;
    }

    /**
     * @param string $urlKey
     * @param int|null $storeId
     *
     * @return CategoryBuilder
     */
    public function withUrlKey(string $urlKey, ?int $storeId = null): CategoryBuilder
    {
        $builder = clone $this;
        if ($storeId) {
            $builder->storeSpecificValues[$storeId]['url_key'] = $urlKey;
        } else {
            $builder->category->setData('url_key', $urlKey);
        }

        return $builder;
    }

    /**
     * @param bool $isActive
     * @param int|null $storeId
     *
     * @return CategoryBuilder
     */
    public function withIsActive(bool $isActive, ?int $storeId = null): CategoryBuilder
    {
        $builder = clone $this;
        if ($storeId) {
            $builder->storeSpecificValues[$storeId][CategoryInterface::KEY_IS_ACTIVE] = $isActive;
        } else {
            $builder->category->setIsActive($isActive);
        }

        return $builder;
    }

    /**
     * @param bool $isAnchor
     *
     * @return CategoryBuilder
     */
    public function withIsAnchor(bool $isAnchor): CategoryBuilder
    {
        $builder = clone $this;
        $builder->category->setData('is_anchor', $isAnchor);

        return $builder;
    }

    /**
     * @param string $displayMode
     *
     * @return CategoryBuilder
     */
    public function withDisplayMode(string $displayMode): CategoryBuilder
    {
        $builder = clone $this;
        $builder->category->setData('display_mode', $displayMode);

        return $builder;
    }

    /**
     * @param int $storeId
     *
     * @return CategoryBuilder
     */
    public function withStoreId(int $storeId): CategoryBuilder
    {
        $builder = clone $this;
        $builder->category->setData('store_id', $storeId);

        return $builder;
    }

    /**
     * @param mixed[] $values
     * @param int|null $storeId
     *
     * @return CategoryBuilder
     */
    public function withCustomAttributes(array $values, ?int $storeId = null): CategoryBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            if ($storeId) {
                $builder->storeSpecificValues[$storeId][$code] = $value;
            } else {
                $builder->category->setCustomAttribute($code, $value);
            }
        }

        return $builder;
    }

    /**
     * @param string $fileName
     *
     * @return $this
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function withImage(string $fileName): CategoryBuilder
    {
        $builder = clone $this;

        $objectManager = Bootstrap::getObjectManager();
        $dbStorage = $objectManager->create(Database::class);
        $filesystem = $objectManager->get(Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $directory = $objectManager->get(Directory::class);
        $imageUploader = $objectManager->create(
            ImageUploader::class,
            [
                'baseTmpPath' => 'catalog/tmp/category',
                'basePath' => 'media/catalog/category',
                'coreFileStorageDatabase' => $dbStorage,
                'allowedExtensions' => ['jpg', 'jpeg', 'gif', 'png'],
                'allowedMimeTypes' => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'],
            ],
        );

        $fixtureImagePath = $directory->getDir(moduleName: 'Klevu_TestFixtures')
            . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . $fileName;
        $tmpFilePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        copy(from: $fixtureImagePath, to: $tmpFilePath);
        // phpcs:ignore Magento2.Security.Superglobal.SuperglobalUsageError, SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFilePath,
            'error' => 0,
            'size' => 12500,
        ];
        $imageUploader->saveFileToTmpDir(fileId: 'image');
        $imagePath = $imageUploader->moveFileFromTmp(imageName: $fileName, returnRelativePath: true);

        $builder->category->setData('image', $imagePath);

        return $builder;
    }

    /**
     * @return CategoryInterface
     * @throws \Exception
     */
    public function build(): CategoryInterface
    {
        $builder = clone $this;

        if (!$builder->category->getData('url_key')) {
            $builder->category->setData('url_key', sha1(uniqid('', true)));
        }
        // Save with global scope if not specified otherwise
        if (!$builder->category->hasData('store_id')) {
            $builder->category->setStoreId(0);
        }
        /** @var Category $category */
        $category = $builder->category;
        $builder->categoryResource->save($category);

        foreach ($builder->skus as $position => $sku) {
            $productLink = $builder->productLinkFactory->create();
            $productLink->setSku($sku);
            $productLink->setPosition($position);
            $productLink->setCategoryId($builder->category->getId());
            $builder->categoryLinkRepository->save($productLink);
        }
        foreach ($builder->storeSpecificValues as $storeId => $values) {
            $storeCategory = clone $category;
            $storeCategory->setStoreId($storeId);
            $storeCategory->addData($values);
            $builder->categoryResource->save($storeCategory);
        }
        if ($builder->storeSpecificValues) {
            $this->clearCategoryRepositoryCache();
        }

        return $builder->category;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->category = clone $this->category;
    }

    /**
     * @return void
     */
    private function clearCategoryRepositoryCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
        $categoryRepository->_resetState();
    }
}
