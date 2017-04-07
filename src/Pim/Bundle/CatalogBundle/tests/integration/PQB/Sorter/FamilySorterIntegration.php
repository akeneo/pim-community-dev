<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\Sorter\Directions;

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
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $familyB = $this->get('pim_catalog.factory.family')->create();
            $family1 = $this->get('pim_catalog.factory.family')->create();
            $family2 = $this->get('pim_catalog.factory.family')->create();

            $this->get('pim_catalog.updater.family')->update($familyB, ['code' => 'familyB']);
            $this->get('pim_catalog.updater.family')->update($family1, ['code' => 'family1']);
            $this->get('pim_catalog.updater.family')->update($family2, ['code' => 'family2']);

            $this->get('pim_catalog.saver.family')->saveAll([$familyB, $family1, $family2]);

            $this->createProduct('fooA', ['family' => 'familyA']);
            $this->createProduct('fooB', ['family' => 'familyB']);
            $this->createProduct('foo1', ['family' => 'family1']);
            $this->createProduct('foo2', ['family' => 'family2']);
            $this->createProduct('baz', []);
        }
    }

    public function testSortDescendant()
    {
        $result = $this->executeSorter([['family', Directions::DESCENDING]]);
        $this->assertOrder($result, ['fooB', 'fooA', 'foo2', 'foo1', 'baz']);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['family', Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo1', 'foo2', 'fooA', 'fooB', 'baz']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['family', 'A_BAD_DIRECTION']]);
    }
}
