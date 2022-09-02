<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Number;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Number sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'             => 'a_scopable_number',
            'type'             => AttributeTypes::NUMBER,
            'localizable'      => false,
            'scopable'         => true,
            'negative_allowed' => true,
            'decimals_allowed' => true,
        ]);

        $this->createProduct('product_one', [
            new SetNumberValue('a_scopable_number', 'ecommerce', null, '192.103'),
            new SetNumberValue('a_scopable_number', 'tablet', null, '16'),
        ]);

        $this->createProduct('product_two', [
            new SetNumberValue('a_scopable_number', 'ecommerce', null, '16'),
            new SetNumberValue('a_scopable_number', 'tablet', null, '192.103'),
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_scopable_number', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_number', Directions::ASCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_scopable_number', Directions::DESCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_number', Directions::DESCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_scopable_number', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }
}
