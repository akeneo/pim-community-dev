<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Option;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Option attribute sorter integration tests (simple select)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttributeOption([
            'attribute' => 'a_simple_select',
            'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_simple_select',
            'code'      => 'black'
        ]);

        $this->createProduct('product_one', [
            new SetSimpleSelectValue('a_simple_select', null, null, 'black'),
        ]);

        $this->createProduct('product_two', [
            new SetSimpleSelectValue('a_simple_select', null, null, 'orange'),
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_simple_select', Directions::ASCENDING]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_simple_select', Directions::DESCENDING]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_simple_select', 'A_BAD_DIRECTION']]);
    }
}
