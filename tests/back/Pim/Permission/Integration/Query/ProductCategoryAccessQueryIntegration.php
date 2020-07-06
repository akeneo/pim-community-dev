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
    }

    public function test_it_returns_not_categorized_products_and_filter_not_granted_products()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $productIdentifiers = $this->getQuery()->getGrantedProductIdentifiers([
            'product_without_category',
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'unknown_product'
        ], $user);

        $productIdentifiersExpected = [
            'product_viewable_by_everybody',
            'product_without_category',
        ];

        $this->assertEquals($productIdentifiersExpected, $productIdentifiers);

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

        $this->assertEquals($productIdentifiersExpected, $productIdentifiers);
    }

    private function createProduct(string $identifier, array $data = []): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    protected function getQuery(): ProductCategoryAccessQueryInterface
    {
        return $this->get('pimee_security.query.product_category_access_with_ids');
    }
}
