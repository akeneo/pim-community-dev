<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StatusSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('foo', ['enabled' => false]);
        $this->createProduct('bar', ['enabled' => true]);
        $this->createProduct('baz', ['enabled' => false]);
        $this->createProduct('foobar', ['enabled' => true]);
        $this->createProduct('foobaz', []);
    }

    public function testSortDescendant()
    {
        $result = $this->executeSorter([['enabled', Directions::DESCENDING]]);
        $this->assertOrder($result, ['bar', 'foobar', 'foobaz', 'foo', 'baz']);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['enabled', Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo', 'baz', 'bar', 'foobar', 'foobaz']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['enabled', 'A_BAD_DIRECTION']]);
    }
}
