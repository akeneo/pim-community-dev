<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\tests\Integration\Storage\ElasticsearchAndSql\CategoryTree;

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

        $this->container->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }
}
