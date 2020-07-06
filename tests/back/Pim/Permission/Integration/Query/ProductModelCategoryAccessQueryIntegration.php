<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class ProductModelCategoryAccessQueryIntegration extends TestCase
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
        $this->createProductModel('product_model_without_category', [
            'family_variant' => 'familyVariantA1',
        ]);
        $this->createProductModel('product_model_viewable_by_everybody', [
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryA2']
        ]);

        $this->createProductModel('product_model_not_viewable_by_redactor', [
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryB']
        ]);
    }

    public function test_it_returns_not_categorized_products_and_filter_not_granted_products()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $productModelCodes = $this->getQuery()->getGrantedProductModelCodes([
            'product_model_without_category',
            'product_model_viewable_by_everybody',
            'product_model_not_viewable_by_redactor',
            'unknown_product_model'
        ], $user);

        $productModelCodesExpected = [
            'product_model_viewable_by_everybody',
            'product_model_without_category',
        ];

        $this->assertEquals($productModelCodesExpected, $productModelCodes);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $productModelCodes = $this->getQuery()->getGrantedProductModelCodes([
            'product_model_without_category',
            'product_model_viewable_by_everybody',
            'product_model_not_viewable_by_redactor',
            'unknown_product_model'
        ], $user);

        $productModelCodesExpected = [
            'product_model_viewable_by_everybody',
            'product_model_not_viewable_by_redactor',
            'product_model_without_category',
        ];

        $this->assertEquals($productModelCodesExpected, $productModelCodes);
    }

    protected function createProductModel(string $code, array $data = []): void
    {
        $productModel = new ProductModel();
        $productModel->setCode($code);

        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();
    }

    protected function getQuery(): ProductModelCategoryAccessQueryInterface
    {
        return $this->get('pimee_security.query.product_model_category_access_with_ids');
    }
}
