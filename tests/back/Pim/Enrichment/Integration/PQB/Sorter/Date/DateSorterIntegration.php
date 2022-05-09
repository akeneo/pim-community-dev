<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Date sorter integration tests.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_date', Directions::ASCENDING]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_date', Directions::DESCENDING]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_date', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_one', [
            new SetDateValue('a_date', null, null, new \DateTime('2017-04-11'))
        ]);

        $this->createProduct('product_two', [
            new SetDateValue('a_date', null, null, new \DateTime('2016-03-10'))
        ]);

        $this->createProduct('product_three', [
            new SetDateValue('a_date', null, null, new \DateTime('2015-02-09'))
        ]);

        $this->createProduct('empty_product', []);
    }
}
