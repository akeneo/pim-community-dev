<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('fooA', ['family' => 'familyA']);
        $this->createProduct('fooA1', ['family' => 'familyA1']);
        $this->createProduct('fooA2', ['family' => 'familyA2']);
        $this->createProduct('baz', []);
    }

    public function testSortCodeDescendant()
    {
        $result = $this->executeSorter([['family', Directions::DESCENDING]]);
        $this->assertOrder($result, ['fooA2', 'fooA1', 'fooA', 'baz']);
    }

    public function testSortLabelDescendant()
    {
        $result = $this->executeSorter([['family', Directions::DESCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['fooA1', 'fooA', 'fooA2', 'baz']);

        $result = $this->executeSorter([['family', Directions::DESCENDING, ['locale'=> 'fr_FR']]]);
        $this->assertOrder($result, ['fooA', 'fooA2', 'fooA1', 'baz']);
    }

    public function testSortCodeAscendant()
    {
        $result = $this->executeSorter([['family', Directions::ASCENDING]]);
        $this->assertOrder($result, ['fooA', 'fooA1', 'fooA2', 'baz']);
    }

    public function testSortLabelAscendant()
    {
        $result = $this->executeSorter([['family', Directions::ASCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['fooA', 'fooA1', 'fooA2', 'baz']);

        $result = $this->executeSorter([['family', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['fooA', 'fooA1', 'fooA2', 'baz']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['family', 'A_BAD_DIRECTION']]);
    }
}
