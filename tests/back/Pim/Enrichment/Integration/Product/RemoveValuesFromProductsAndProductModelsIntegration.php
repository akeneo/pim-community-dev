<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProductModels;
use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProducts;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

class RemoveValuesFromProductsAndProductModelsIntegration extends TestCase
{
    use AssertEventCountTrait;

    /**
     * @test
     */
    public function it_cleans_the_removed_attribute_values_in_products()
    {
        $niceAttribute = $this->createAttribute('nice_attribute');
        $otherAttribute = $this->createAttribute('other_attribute');

        $product1 = $this->createProductWithAttributeValue('product_one', 'nice_attribute', 'value one');
        $product2 = $this->createProductWithAttributeValue('product_two', 'nice_attribute', 'value two');
        $product3 = $this->createProductWithAttributeValue('product_three', 'other_attribute', 'value three');
        $product4 = $this->createProductWithAttributeValue('product_four', 'other_attribute', 'value four');

        $this->deleteAttribute($niceAttribute);
        $this->deleteAttribute($otherAttribute);

        $attributeCodes = [
            'nice_attribute',
            'other_attribute'
        ];
        $productUuids = [
            $product1->getUuid()->toString(),
            $product2->getUuid()->toString(),
            $product3->getUuid()->toString(),
            $product4->getUuid()->toString(),
        ];

        $this->assertEquals(4, $this->getProductWithAttributeValuesCount($attributeCodes, $productUuids));

        $this->getRemoveValuesFromProducts()
            ->forAttributeCodes($attributeCodes, $productUuids);

        $this->assertEquals(0, $this->getProductWithAttributeValuesCount($attributeCodes, $productUuids));

        $this->assertEventCount(4, ProductUpdated::class);
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

    private function getProductWithAttributeValuesCount(array $attributeCodes, array $productUuids): int
    {
        $connection = $this->get('database_connection');

        $uuidsAsBytes = \array_map(fn($productUuid) => Uuid::fromString($productUuid)->getBytes(), $productUuids);
        $paths = implode(
            ',',
            array_map(fn ($attributeCode) => $connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $result = $connection->executeQuery(
            <<<SQL
SELECT COUNT(id)
FROM `pim_catalog_product`
WHERE JSON_CONTAINS_PATH(raw_values, 'one', $paths)
AND uuid IN (:product_uuids)
SQL,
            [
                'product_uuids' => $uuidsAsBytes
            ],
            [

                'product_uuids' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchOne();

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
        )->fetchOne();

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
    ): ProductInterface {
        $product = $this->getProductBuilder()
            ->withIdentifier($identifier)
            ->withValue($attributeCode, $valueData)->build();
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
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
