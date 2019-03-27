<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('foo', []);
        $this->createProduct('bar', []);
        $this->createProduct('baz', []);
        $this->createProduct('BARISTA', []);
        $this->createProduct('BAZAR', []);
    }

    public function testOperatorAscending()
    {
        $result = $this->executeSorter([['identifier', Directions::ASCENDING]]);
        $this->assert($result, ['bar', 'BARISTA', 'baz', 'BAZAR', 'foo']);

        $result = $this->executeSorter([['sku', Directions::ASCENDING]]);
        $this->assert($result, ['bar', 'BARISTA', 'baz', 'BAZAR', 'foo']);
    }

    public function testOperatorDescending()
    {
        $result = $this->executeSorter([['identifier', Directions::ASCENDING]]);
        $this->assert($result, ['foo', 'BAZAR', 'baz', 'BARISTA', 'bar']);

        $result = $this->executeSorter([['sku', Directions::ASCENDING]]);
        $this->assert($result, ['foo', 'BAZAR', 'baz', 'BARISTA', 'bar']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['identifier', 'A_BAD_DIRECTION']]);
    }
}
