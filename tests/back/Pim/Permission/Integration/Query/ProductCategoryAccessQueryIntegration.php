<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Query;

use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class ProductCategoryAccessQueryIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProduct('product_without_category', []);
        $this->createProduct('product_viewable_by_everybody', [
            'categories' => ['categoryA2']
        ]);

        $this->createProduct('product_not_viewable_by_redactor', [
            'categories' => ['categoryB']
        ]);
        $this->createProductModel('product_model_with_not_viewable_categories', ['categoryB']);
        $this->createProductModel('product_model_with_viewable_categories', ['categoryA2']);
        $this->createProduct('product_with_product_model_not_viewable_by_redactor', [
            'categories' => [],
            'parent' => 'product_model_with_not_viewable_categories'
        ]);
        $this->createProduct('product_with_product_model_viewable_by_redactor', [
            'categories' => ['categoryB'],
            'parent' => 'product_model_with_viewable_categories'
        ]);
    }

    public function test_it_returns_not_categorized_products_and_filter_not_granted_products()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $productIdentifiers = $this->getQuery()->getGrantedProductIdentifiers([
            'product_without_category',
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'unknown_product',
            'product_with_product_model_not_viewable_by_redactor',
            'product_with_product_model_viewable_by_redactor',
        ], $user);

        $productIdentifiersExpected = [
            'product_viewable_by_everybody',
            'product_without_category',
            'product_with_product_model_viewable_by_redactor'
        ];

        $this->assertEqualsCanonicalizing($productIdentifiersExpected, $productIdentifiers);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $productIdentifiers = $this->getQuery()->getGrantedProductIdentifiers([
            'product_without_category',
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'unknown_product'
        ], $user);

        $productIdentifiersExpected = [
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'product_without_category',
        ];

        $this->assertEqualsCanonicalizing($productIdentifiersExpected, $productIdentifiers);
    }

    private function createProduct(string $identifier, array $data = []): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createProductModel(string $code, array $categoryCodes): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create($code);
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'categories' => $categoryCodes,
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']]
                ]
            ]
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getQuery(): ProductCategoryAccessQueryInterface
    {
        return $this->get('pimee_security.query.product_category_access_with_ids');
    }
}
