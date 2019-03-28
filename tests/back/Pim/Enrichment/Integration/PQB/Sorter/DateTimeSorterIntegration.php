<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('foo', []);
        sleep(2);
        $this->createProduct('bar', []);
        sleep(2);
        $this->createProduct('baz', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['updated', Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo', 'bar', 'baz']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['updated', Directions::DESCENDING]]);
        $this->assertOrder($result, ['baz', 'bar', 'foo']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['updated', 'A_BAD_DIRECTION']]);
    }
}
