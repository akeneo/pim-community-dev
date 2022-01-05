<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\TableAttribute\Integration\Value\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use PHPUnit\Framework\Assert;

final class GetProductIdentifiersWithRemovedAttributesIntegration extends TestCase
{
    use EntityBuilderTrait;

    private GetProductIdentifiersWithRemovedAttributeInterface $query;

    /** @test */
    public function it_gets_product_identifiers_with_removed_attributes(): void
    {
        Assert::assertSame([['test1']], \iterator_to_array($this->query->nextBatch(['name'], 100)));
    }

    /** @test */
    public function it_gets_product_identifiers_with_removed_table_attributes(): void
    {
        Assert::assertSame([['test2', 'test4']], \iterator_to_array($this->query->nextBatch(['nutrition', 'nutrition2'], 100)));
    }

    /** @test */
    public function it_gets_product_identifiers_with_removed_attributes_with_any_attribute(): void
    {
        Assert::assertSame([['test1', 'test2', 'test4']], \iterator_to_array($this->query->nextBatch(['name', 'nutrition', 'nutrition2'], 100)));
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
        $this->query = $this->get(
            'akeneo.pim.enrichment.product.query.get_product_identifiers_with_removed_attribute'
        );
    }
}
