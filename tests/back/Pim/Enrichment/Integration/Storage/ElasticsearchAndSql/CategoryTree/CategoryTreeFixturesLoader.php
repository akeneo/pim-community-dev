<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryTreeFixturesLoader
{
    /** @var SimpleFactoryInterface */
    private $categoryFactory;
    /** @var ObjectUpdaterInterface */
    private $categoryUpdater;
    /** @var ValidatorInterface */
    private $validator;
    /** @var SaverInterface */
    private $categorySaver;
    /** @var ProductBuilderInterface */
    private $productBuilder;
    /** @var ObjectUpdaterInterface */
    private $productUpdater;
    /** @var ValidatorInterface */
    private $productValidator;
    /** @var SaverInterface */
    private $productSaver;
    /** @var SimpleFactoryInterface */
    private $productModelFactory;
    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;
    /** @var ValidatorInterface */
    private $productModelValidator;
    /** @var SaverInterface */
    private $productModelSaver;
    /** @var SimpleFactoryInterface */
    private $familyFactory;
    /** @var ObjectUpdaterInterface */
    private $familyUpdater;
    /** @var SaverInterface */
    private $familySaver;
    /** @var SimpleFactoryInterface */
    private $familyVariantFactory;
    /** @var ObjectUpdaterInterface */
    private $familyVariantUpdater;
    /** @var SaverInterface */
    private $familyVariantSaver;
    /** @var SimpleFactoryInterface */
    private $attributeFactory;
    /** @var ObjectUpdaterInterface */
    private $attributeUpdater;
    /** @var SaverInterface */
    private $attributeSaver;
    /** @var Client */
    private $esClient;

    public function __construct(
        SimpleFactoryInterface $categoryFactory,
        ObjectUpdaterInterface $categoryUpdater,
        SaverInterface $categorySaver,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        SaverInterface $productSaver,
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        ValidatorInterface $productModelValidator,
        SaverInterface $productModelSaver,
        SimpleFactoryInterface $familyFactory,
        ObjectUpdaterInterface $familyUpdater,
        SaverInterface $familySaver,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $familyVariantUpdater,
        SaverInterface $familyVariantSaver,
        SimpleFactoryInterface $attributeFactory,
        ObjectUpdaterInterface $attributeUpdater,
        SaverInterface $attributeSaver,
        ValidatorInterface $validator,
        Client $esClient
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryUpdater = $categoryUpdater;
        $this->validator = $validator;
        $this->categorySaver = $categorySaver;
        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver = $productSaver;
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelValidator = $productModelValidator;
        $this->productModelSaver = $productModelSaver;
        $this->familyFactory = $familyFactory;
        $this->familyUpdater = $familyUpdater;
        $this->familySaver = $familySaver;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->familyVariantUpdater = $familyVariantUpdater;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeSaver = $attributeSaver;
        $this->esClient = $esClient;
    }

    /**
     * @param array       $categories
     * @param null|string $parentCode
     */
    public function givenTheCategoryTrees(array $categories, ?string $parentCode = null): void
    {
        foreach ($categories as $categoryCode => $children) {
            $category = $this->categoryFactory->create();
            $this->categoryUpdater->update(
                $category,
                [
                    'code'   => $categoryCode,
                    'parent' => $parentCode ?? null,
                    'labels' => ['en_US' => ucfirst($categoryCode)]
                ]
            );
            Assert::assertEquals(0, $this->validator->validate($category)->count());
            $this->categorySaver->save($category);

            $this->givenTheCategoryTrees($children, $categoryCode);
        }
    }

    /**
     * @param array $products
     */
    public function givenTheProductsWithCategories(array $products): void
    {
        foreach ($products as $identifier => $categories) {
            $product = $this->productBuilder->createProduct($identifier);
            $this->productUpdater->update(
                $product,
                [
                    'categories' => $categories
                ]
            );
            $constraintList = $this->productValidator->validate($product);
            Assert::assertEquals(0, $constraintList->count());
            $this->productSaver->save($product);
        }

        $this->esClient->refreshIndex();
    }

    /**
     * @param array $products
     */
    public function givenTheProductModelsWithCategories(array $categoryCodes): void
    {
        $this->createFamily();
        $this->createFamilyVariant();

        foreach ($categoryCodes as $identifier => $categories) {
            $productModel = $this->productModelFactory->create();
            $this->productModelUpdater->update(
                $productModel,
                [
                    'categories'     => $categories,
                    'code'           => 'product_model_' . $identifier,
                    'family_variant' => 'family_variant',
                    'values'         => []
                ]
            );
            $constraintList = $this->productModelValidator->validate($productModel);
            Assert::assertEquals(0, $constraintList->count());
            $this->productModelSaver->save($productModel);
        }

        $this->esClient->refreshIndex();
    }

    private function createFamily(): void
    {
        $this->createAttribute([
            'code'              => 'name',
            'type'              => 'pim_catalog_text',
            'localizable'       => false,
            'scopable'          => false,
        ]);

        $family = $this->familyFactory->create();
        $this->familyUpdater->update($family, [
            'code'        => 'family_for_pm',
            'attributes'  => ['sku', 'name']
        ]);

        $this->familySaver->save($family);
    }

    private function createFamilyVariant(): void
    {
        $this->createAttribute([
            'code'              => 'size',
            'type'              => 'pim_catalog_boolean',
            'localizable'       => false,
            'scopable'          => false,
        ]);

        $family = $this->familyVariantFactory->create();
        $this->familyVariantUpdater->update($family, [
            'code'        => 'family_variant',
            'family'      => 'family_for_pm',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['size'],
                ],
            ]
        ]);
        $this->familyVariantSaver->save($family);
    }

    private function createAttribute(array $data): void
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeUpdater->update($attribute, $data);
        $this->attributeSaver->save($attribute);
    }
}
