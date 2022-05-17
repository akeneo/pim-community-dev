<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class ProductValueIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attribute = new Attribute();
        $attribute->setEntityType(Product::class);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                ['code' => 'ingredient', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'], 'options' => [['code' => 'bar']]],
                ['code' => 'quantity', 'data_type' => 'number', 'labels' => ['en_US' => 'Quantity']],
                ['code' => 'manufacturing_time', 'data_type' => 'measurement', 'measurement_family_code' => 'Duration', 'measurement_default_unit_code' => 'DAY'],
            ],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /** @test */
    public function it_updates_and_validates_then_saves_a_table_product_value(): void
    {
        /** @var Product $product */
        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'NUTRITION' => [
                ['locale' => null, 'scope' => null, 'data' => [
                    ['INGredient' => 'BAR', 'quantity' => 10, 'manufacturing_time' => ['unit' => 'DAY', 'amount' => 10]],
                ]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
        $this->assertProductIsInDatabase($product);
        $this->assertIndexingFormat(\sprintf('product_%s', $product->getUuid()->toString()));
        $this->assertValuesAreSanitized();
    }

    /** @test */
    public function it_updates_and_validates_then_saves_a_table_product_model_value(): void
    {
        $this->createAttribute([
            'code' => 'size',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createAttributeOption(['attribute' => 'size', 'code' => 's']);

        $this->createFamily([
            'code' => 'shoes',
            'attributes' => ['sku', 'size', 'nutrition'],
            'attribute_requirements' => [],
        ]);

        $this->createFamilyVariant([
            'code' => 'shoe_size',
            'family' => 'shoes',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['size'],
                ],
            ],
        ]);

        $productModel = new ProductModel();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => 'pm1',
            'parent' => null,
            'family_variant' => 'shoe_size',
            'values' => [
                'NUTRITION' => [
                    ['locale' => null, 'scope' => null, 'data' => [
                        ['INGredient' => 'BAR', 'quantity' => 10, 'manufacturing_time' => ['unit' => 'DAY', 'amount' => 10]]],
                    ],
                ],
            ],
        ]);
        self::assertInstanceOf(TableValue::class, $productModel->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        self::assertCount(0, $violations, sprintf('Product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->assertProductModelIsInDatabase($productModel);
        $this->assertIndexingFormat(\sprintf('product_model_%d', $productModel->getId()));
    }

    private function assertProductIsInDatabase(ProductInterface $product): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $rawValues = $connection->executeQuery(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $product->getIdentifier()]
        )->fetchOne();
        self::assertNotNull($rawValues);
        self::assertJsonStringEqualsJsonString(\json_encode($product->getRawValues()), $rawValues);

        $rawValues = \json_decode($rawValues, true);
        $nutrition = $rawValues['nutrition']['<all_channels>']['<all_locales>'] ?? null;
        self::assertNotNull($nutrition);
        self::assertIsArray($nutrition);
        self::assertCount(1, $nutrition);
        self::assertCount(3, $nutrition[0]);
        foreach ($nutrition[0] as $columnId => $value) {
            self::assertDoesNotMatchRegularExpression(
                '/^(quantity|ingredient|manufacturing_time)$/',
                $columnId,
                'The key should not be the code but the id'
            );
            self::assertMatchesRegularExpression(
                '/^(quantity|ingredient|manufacturing_time)_[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}$/',
                $columnId,
                'The id is malformed'
            );
        }
    }

    private function assertProductModelIsInDatabase(ProductModelInterface $productModel): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $rawValues = $connection->executeQuery(
            'SELECT raw_values FROM pim_catalog_product_model WHERE code = :code',
            ['code' => $productModel->getCode()]
        )->fetchOne();
        self::assertNotNull($rawValues);
        self::assertJsonStringEqualsJsonString(\json_encode($productModel->getRawValues()), $rawValues);

        $rawValues = \json_decode($rawValues, true);
        $nutrition = $rawValues['nutrition']['<all_channels>']['<all_locales>'] ?? null;
        self::assertNotNull($nutrition);
        self::assertIsArray($nutrition);
        self::assertCount(1, $nutrition);
        self::assertCount(3, $nutrition[0]);
        foreach ($nutrition[0] as $columnId => $value) {
            self::assertDoesNotMatchRegularExpression(
                '/^(quantity|ingredient|manufacturing_time)$/',
                $columnId,
                'The key should not be the code but the id'
            );
            self::assertMatchesRegularExpression(
                '/^(quantity|ingredient|manufacturing_time)_[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}$/',
                $columnId,
                'The id is malformed'
            );
        }
    }

    private function assertIndexingFormat(string $esIdentifier): void
    {
        /** @var Client $productClient */
        $productClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $productClient->refreshIndex();

        $res = $productClient->get($esIdentifier);
        Assert::assertTrue($res['found']);

        $indexedProduct = $res['_source'];
        Assert::assertArrayNotHasKey('nutrition-table', $indexedProduct['values']);
        Assert::assertArrayHasKey('table_values', $indexedProduct);
        Assert::assertArrayHasKey('nutrition', $indexedProduct['table_values']);

        $expectedTableValues = [
            [
                'row' => 'bar',
                'column' => 'quantity',
                'value-number' => 10,
                'is_column_complete' => true,
            ],
            [
                'row' => 'bar',
                'column' => 'ingredient',
                'value-select' => 'bar',
                'is_column_complete' => true,
            ],
            [
                'row' => 'bar',
                'column' => 'manufacturing_time',
                'value-measurement' => 864000.0, // 10 days = 864000 seconds
                'is_column_complete' => true,
            ],
        ];

        Assert::assertEqualsCanonicalizing($expectedTableValues, $indexedProduct['table_values']['nutrition']);
    }

    private function createAttribute(array $data): void
    {
        $data['group'] ??= 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOption(array $data): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    private function assertValuesAreSanitized(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        $tableValue = $product->getValue('nutrition');

        self::assertNotNull($tableValue);
        self::assertEqualsCanonicalizing(
            ['bar', 10, ['unit' => 'DAY', 'amount' => 10]],
            \array_values($tableValue->getData()->normalize()[0])
        );
    }
}
