<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree;

use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class CategoryTreeFixturesLoader
{
    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array       $categories
     * @param null|string $parentCode
     */
    public function givenTheCategoryTrees(array $categories, ?string $parentCode = null): void
    {
        foreach ($categories as $categoryCode => $children) {
            $category = $this->container->get('pim_catalog.factory.category')->create();
            $this->container->get('pim_catalog.updater.category')->update($category, [
                'code' => $categoryCode,
                'parent' => $parentCode ?? null,
                'labels' => ['en_US' => ucfirst($categoryCode)]
            ]);
            Assert::assertEquals(0, $this->container->get('validator')->validate($category)->count());
            $this->container->get('pim_catalog.saver.category')->save($category);

            $this->givenTheCategoryTrees($children, $categoryCode);
        }
    }

    /**
     * @param array $products
     */
    public function givenTheProductsWithCategories(array $products): void
    {
        foreach ($products as $identifier => $categories) {
            $product = $this->container->get('pim_catalog.builder.product')->createProduct($identifier);
            $this->container->get('pim_catalog.updater.product')->update($product, [
                'categories' => $categories
            ]);
            $constraintList = $this->container->get('pim_catalog.validator.product')->validate($product);
            Assert::assertEquals(0, $constraintList->count());
            $this->container->get('pim_catalog.saver.product')->save($product);
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @param array $products
     */
    public function givenTheProductModelsWithCategories(array $products): void
    {
        $this->createFamily();
        $this->createFamilyVariant();

        foreach ($products as $identifier => $categories) {
            $productModel = $this->container->get('pim_catalog.factory.product_model')->create();
            $this->container->get('pim_catalog.updater.product_model')->update($productModel, [
                'categories' => $categories,
                'code' => 'product_model_'.$identifier,
                'family_variant' => 'family_variant',
                'values'  => []
            ]);
            $constraintList = $this->container->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::assertEquals(0, $constraintList->count());
            $this->container->get('pim_catalog.saver.product_model')->save($productModel);
        }

        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createFamily(): void
    {
        $this->createAttribute([
            'code'              => 'name',
            'type'              => 'pim_catalog_text',
            'localizable'       => false,
            'scopable'          => false,
        ]);

        $family = $this->container->get('pim_catalog.factory.family')->create();
        $this->container->get('pim_catalog.updater.family')->update($family, [
            'code'        => 'family_for_pm',
            'attributes'  => ['sku', 'name']
        ]);

        $this->container->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(): void
    {
        $this->createAttribute([
            'code'              => 'size',
            'type'              => 'pim_catalog_boolean',
            'localizable'       => false,
            'scopable'          => false,
        ]);

        $family = $this->container->get('pim_catalog.factory.family_variant')->create();
        $this->container->get('pim_catalog.updater.family_variant')->update($family, [
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
        $this->container->get('pim_catalog.saver.family_variant')->save($family);
    }

    private function createAttribute(array $data): void
    {
        $attribute = $this->container->get('pim_catalog.factory.attribute')->create();
        $this->container->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->container->get('pim_catalog.saver.attribute')->save($attribute);

    }
}
