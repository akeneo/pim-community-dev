<?php

namespace Pim\Bundle\EnrichBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InGroupSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSortDescendant()
    {
        $result = $this->executeSorter([['in_group_4', Directions::DESCENDING]]);
        $this->assertOrder($result, ['foo', 'bar', 'baz', 'empty']);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['in_group_4', Directions::ASCENDING]]);
        $this->assertOrder($result, ['baz', 'empty', 'foo', 'bar']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['in_group_4', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $group = $this->get('pim_catalog.factory.group')->create();
            $this->get('pim_catalog.updater.group')->update(
                $group,
                [
                    'code' => 'groupC',
                    'type' => 'RELATED',
                ]
            );
            $this->get('pim_catalog.saver.group')->save($group);

            $this->createProduct('foo', ['groups' => ['groupA', 'groupB']]);
            $this->createProduct('bar', ['groups' => ['groupB']]);
            $this->createProduct('baz', ['groups' => ['groupC']]);
            $this->createProduct('empty', []);
        }
    }

}
