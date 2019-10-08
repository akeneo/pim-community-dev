<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class GetAncestorProductModelCodesIntegration extends TestCase
{
    public function test_that_it_returns_an_empty_array_for_simple_products()
    {
        Assert::assertSame(
            [],
            $this->get('akeneo.pim.enrichment.product.query.get_ancestor_product_model_codes')
                ->fromProductIdentifiers(['simple_product', 'another_product'])
        );
    }

    public function test_that_it_returns_ancestor_codes_of_variant_products()
    {
        Assert::assertEqualsCanonicalizing(
            ['root_A1', 'subpm_A1_optionA', 'root_A2'],
            $this->get('akeneo.pim.enrichment.product.query.get_ancestor_product_model_codes')
                 ->fromProductIdentifiers(['simple_product', 'variant_A1_A_no', 'variant_A1_A_yes', 'variant_A2_B_no'])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel(['code' => 'root_A1', 'family_variant' => 'familyVariantA1']);
        $this->createProductModel([
            'code' => 'subpm_A1_optionA',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_A1',
            'values' => [
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
            ],
        ]);
        $this->createProduct('variant_A1_A_yes', [
            'parent' => 'subpm_A1_optionA',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
            ],
        ]);
        $this->createProduct('variant_A1_A_no', [
            'parent' => 'subpm_A1_optionA',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => false]],
            ],
        ]);
        $this->createProductModel([
            'code' => 'subpm_A1_optionB',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_A1',
            'values' => [
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionB']]
            ]
        ]);
        $this->createProduct('variant_A1_B_yes', [
            'parent' => 'subpm_A1_optionB',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
            ],
        ]);
        $this->createProduct('variant_A1_B_no', [
            'parent' => 'subpm_A1_optionB',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => false]],
            ],
        ]);
        $this->createProductModel(['code' => 'root_A2', 'family_variant' => 'familyVariantA2']);
        $this->createProduct('variant_A2_A_yes', [
            'parent' => 'root_A2',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
            ],
        ]);
        $this->createProduct('variant_A2_B_no', [
            'parent' => 'root_A2',
            'values' => [
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => false]],
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionB']],
            ],
        ]);
        $this->createProduct('simple_product', ['family' => 'familyA3']);
        $this->createProduct('another_product', ['family' => 'familyA2']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }
}
