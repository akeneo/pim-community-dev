<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProductModels;
use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProducts;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class RemoveValuesFromProductsAndProductModelsIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_cleans_the_removed_attribute_values_in_products()
    {
        $niceAttribute = $this->createAttribute('nice_attribute');
        $otherAttribute = $this->createAttribute('other_attribute');

        $this->createProductWithAttributeValue('product_one', 'nice_attribute', 'value one');
        $this->createProductWithAttributeValue('product_two', 'nice_attribute', 'value two');
        $this->createProductWithAttributeValue('product_three', 'other_attribute', 'value three');
        $this->createProductWithAttributeValue('product_four', 'other_attribute', 'value four');

        $this->deleteAttribute($niceAttribute);
        $this->deleteAttribute($otherAttribute);

        $attributeCodes = [
            'nice_attribute',
            'other_attribute'
        ];
        $productIdentifiers = [
            'product_one',
            'product_two',
            'product_three',
            'product_four'
        ];

        $this->assertEquals(4, $this->getProductWithAttributeValuesCount($attributeCodes, $productIdentifiers));

        $this->getRemoveValuesFromProducts()
            ->forAttributeCodes($attributeCodes, $productIdentifiers);

        $this->assertEquals(0, $this->getProductWithAttributeValuesCount($attributeCodes, $productIdentifiers));
    }

    /**
     * @test
     */
    public function it_cleans_the_removed_attribute_values_in_product_models()
    {
        $niceAttribute = $this->createAttribute('nice_attribute');
        $otherAttribute = $this->createAttribute('other_attribute');

        $this->createProductModelWithAttributeValue('product_model_one', 'nice_attribute', 'value one');
        $this->createProductModelWithAttributeValue('product_model_two', 'nice_attribute', 'value two');
        $this->createProductModelWithAttributeValue('product_model_three', 'other_attribute', 'value three');
        $this->createProductModelWithAttributeValue('product_model_four', 'other_attribute', 'value four');

        $this->deleteAttribute($niceAttribute);
        $this->deleteAttribute($otherAttribute);

        $attributeCodes = [
            'nice_attribute',
            'other_attribute'
        ];
        $productModelCodes = [
            'product_model_one',
            'product_model_two',
            'product_model_three',
            'product_model_four'
        ];

        $this->assertEquals(4, $this->getProductModelWithAttributeValuesCount($attributeCodes, $productModelCodes));

        $this->getRemoveValuesFromProductModels()
            ->forAttributeCodes($attributeCodes, $productModelCodes);

        $this->assertEquals(0, $this->getProductModelWithAttributeValuesCount($attributeCodes, $productModelCodes));
    }

    private function getProductWithAttributeValuesCount(array $attributeCodes, array $productIdentifiers): int
    {
        $connection = $this->get('database_connection');

        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $result = $connection->executeQuery(
            <<<SQL
SELECT COUNT(id)
FROM `pim_catalog_product`
WHERE JSON_CONTAINS_PATH(raw_values, 'one', $paths)
AND identifier IN (:product_identifiers)
SQL,
            [
                'product_identifiers' => $productIdentifiers
            ],
            [

                'product_identifiers' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchColumn();

        return (int) $result;
    }

    private function getProductModelWithAttributeValuesCount(array $attributeCodes, array $productModelCodes): int
    {
        $connection = $this->get('database_connection');

        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $result = $connection->executeQuery(
            <<<SQL
SELECT COUNT(id)
FROM `pim_catalog_product_model`
WHERE JSON_CONTAINS_PATH(raw_values, 'one', $paths)
AND code IN (:product_model_codes)
SQL,
            [
                'product_model_codes' => $productModelCodes
            ],
            [

                'product_model_codes' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchColumn();

        return (int) $result;
    }

    private function getRemoveValuesFromProducts(): RemoveValuesFromProducts
    {
        return $this->get('pim_catalog.product_values.remove_values_from_products');
    }

    private function getRemoveValuesFromProductModels(): RemoveValuesFromProductModels
    {
        return $this->get('pim_catalog.product_values.remove_values_from_product_models');
    }

    private function createAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->getAttributeBuilder()->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::TEXT,
            'group' => 'other'
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function deleteAttribute(AttributeInterface $attribute): void
    {
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    private function createProductWithAttributeValue(
        string $identifier,
        string $attributeCode,
        string $valueData
    ): void {
        $product = $this->getProductBuilder()
            ->withIdentifier($identifier)
            ->withValue($attributeCode, $valueData)->build();
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createProductModelWithAttributeValue(
        string $code,
        string $attributeCode,
        string $valueData
    ): void {
        $productModel = $this->getProductModelBuilder()
            ->withCode($code)
            ->withFamilyVariant('familyVariantA1')
            ->withValue($attributeCode, $valueData)->build();
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function getAttributeBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.base.attribute.builder');
    }

    private function getProductBuilder(): Builder\Product
    {
        return $this->get('akeneo_integration_tests.catalog.product.builder');
    }

    private function getProductModelBuilder(): Builder\ProductModel
    {
        return $this->get('akeneo_integration_tests.catalog.product_model.builder');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
