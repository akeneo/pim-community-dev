<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Boolean;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Boolean sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('yes', [
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);

        $this->createProduct('no', [
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $this->createProduct('null_product', [
            new ClearValue('a_yes_no', null, null)
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_yes_no', Directions::ASCENDING]]);
        $this->assertOrder($result, ['no', 'yes', 'empty_product', 'null_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_yes_no', Directions::DESCENDING]]);
        $this->assertOrder($result, ['yes', 'no', 'empty_product', 'null_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_yes_no', 'A_BAD_DIRECTION']]);
    }
}
