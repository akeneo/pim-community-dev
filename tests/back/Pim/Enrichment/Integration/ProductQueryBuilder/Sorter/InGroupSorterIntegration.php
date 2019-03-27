<?php

namespace AkeneoTest\Pim\Enrichment\Integration\ProductQueryBuilder\Sorter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InGroupSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSortDescendant()
    {
        $result = $this->executeSorter([['in_group_3', Directions::DESCENDING]]);
        $this->assertOrder($result, ['baz', 'foo', 'bar', 'empty']);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['in_group_3', Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo', 'bar', 'empty', 'baz']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['in_group_3', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

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
