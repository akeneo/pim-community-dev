<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

final class ProductGridFixturesLoader
{
    /** @var  ContainerInterface */
    private $container;

    /** @var string */
    private $akeneoImagePath;

    /**
     * @param ContainerInterface $container
     * @param string             $akeneoImagePath
     */
    public function __construct(ContainerInterface $container, string $akeneoImagePath)
    {
        $this->container = $container;
        $this->akeneoImagePath = $akeneoImagePath;
    }

    public function createProductAndProductModels()
    {
        return [
            'product_models' => $this->createProductModels(),
            'products' => $this->createProducts()
        ];
    }

    private function createProductModels() : array
    {
        $rootProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($rootProductModel, [
            'code' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'an_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => null],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 10],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($rootProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($rootProductModel);

        $subProductModel = $this->container->get('pim_catalog.factory.product_model')->create();
        $this->container->get('pim_catalog.updater.product_model')->update($subProductModel, [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'text'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'optionB'],
                ],
            ]
        ]);

        $errors = $this->container->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->container->get('pim_catalog.saver.product_model')->save($subProductModel);

        return [$rootProductModel, $subProductModel];
    }

    private function createProducts(): array
    {
        $product1 = $this->container->get('pim_catalog.builder.product')->createProduct('foo', 'familyA');
        $this->container->get('pim_catalog.updater.product')->update($product1, [
            'groups' => ['groupA', 'groupB'],
            'parent' => 'sub_product_model',
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $product2 = $this->container->get('pim_catalog.builder.product')->createProduct('baz', null);
        $this->container->get('pim_catalog.updater.product')->update($product2, [
            'values' => [
                'a_localizable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->akeneoImagePath, 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_scopable_image' => [
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => $this->akeneoImagePath, 'locale' => null, 'scope' => 'tablet'],
                ],
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $errors = $this->container->get('validator')->validate($product1);
        Assert::assertCount(0, $errors);
        $errors = $this->container->get('validator')->validate($product2);
        Assert::assertCount(0, $errors);

        $this->container->get('pim_catalog.saver.product')->saveAll([$product1, $product2]);

        return [$product1, $product2];
    }
}
