<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\Sorter\Directions;

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
    protected function setUp()
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

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['identifier', 'A_BAD_DIRECTION']]);
    }
}
