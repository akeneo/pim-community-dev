<?php

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use PHPUnit\Framework\Assert;

final class CountProductsWithRemovedAttributesIntegration extends TestCase
{
    use EntityBuilderTrait;

    private CountProductsWithRemovedAttributeInterface $query;

    /** @test */
    public function it_gets_product_count_with_removed_attributes(): void
    {
        Assert::assertSame(1, $this->query->count(['name']));
    }

    /** @test */
    public function it_gets_product_count_with_removed_table_attributes(): void
    {
        Assert::assertSame(2, $this->query->count(['nutrition', 'nutrition2']));
    }

    /** @test */
    public function it_gets_product_count_with_removed_attributes_with_any_attribute(): void
    {
        Assert::assertSame(3, $this->query->count(['name', 'nutrition', 'nutrition2']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAttribute([
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);
        $this->createAttribute([
            'code' => 'nutrition2',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => [],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'egg'],
                        ['code' => 'butter'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                    'labels' => [],
                ],
            ],
        ]);
        $this->createAttribute([
            'code' => 'name',
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
        ]);
        $this->createProduct('test1', [
            'values' => [
                'name' => [['locale' => null, 'scope' => null, 'data' => 'foo']]
            ]
        ]);
        $this->createProduct('test2', [
            'values' => [
                'nutrition' => [['locale' => null, 'scope' => null, 'data' => [['ingredient' => 'salt', 'quantity' => 10]]]]
            ]
        ]);
        $this->createProduct('test3', []);
        $this->createProduct('test4', [
            'values' => [
                'nutrition2' => [['locale' => null, 'scope' => null, 'data' => [['ingredient' => 'butter', 'quantity' => 20]]]]
            ]
        ]);
        $this->query = $this->get('Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\CountProductsWithRemovedAttributes');
    }
}
