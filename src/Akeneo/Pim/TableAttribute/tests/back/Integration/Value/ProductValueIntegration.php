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

namespace Akeneo\Pim\TableAttribute\tests\back\Integration\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

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
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'], 'options' => [['code' => 'bar']]],
                ['code' => 'quantity', 'data_type' => 'number', 'labels' => ['en_US' => 'Quantity']],
            ],
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /** @test */
    public function it_updates_validates_and_saves_a_table_product_value(): void
    {
        /** @var Product $product */
        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'NUTRITION' => [
                ['locale' => null, 'scope' => null, 'data' => [['INGredients' => 'BAR', 'quantity' => 10]]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
        $this->assertProductIsInDatabase($product);
        $this->assertProductIsInIndex($product);
    }

    private function assertProductIsInDatabase(ProductInterface $product): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');

        $rawValues = $connection->executeQuery(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $product->getIdentifier()]
        )->fetchColumn();
        self::assertNotNull($rawValues);
        self::assertJsonStringEqualsJsonString(\json_encode($product->getRawValues()), $rawValues);
    }

    private function assertProductIsInIndex(ProductInterface $product): void
    {
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $pqb->addFilter('identifier', Operators::EQUALS, 'id1');
        $cursor = $pqb->execute();
        self::assertCount(1, $cursor);

        $productFromIndex = current(\iterator_to_array($cursor));
        self::assertSame('id1', $productFromIndex->getIdentifier());
        self::assertJsonStringEqualsJsonString(
            \json_encode($product->getRawValues()),
            \json_encode($product->getRawValues())
        );
    }
}
