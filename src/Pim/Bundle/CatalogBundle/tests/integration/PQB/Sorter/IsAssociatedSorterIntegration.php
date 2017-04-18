<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IsAssociatedSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSortDescendant()
    {
        $result = $this->executeSorter([['is_associated', Directions::DESCENDING]]);
        $this->assertOrder($result, [
            'foo_bar',
            'foo_baz',
            'foo_bar_baz',
            'foo_group_A',
            'foo_group_B',
            'foo_groups_AB',
            'foo',
            'bar',
            'baz',
        ]);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter([['is_associated', Directions::ASCENDING]]);
        $this->assertOrder($result, [
            'foo',
            'bar',
            'baz',
            'foo_bar',
            'foo_baz',
            'foo_bar_baz',
            'foo_group_A',
            'foo_group_B',
            'foo_groups_AB',
        ]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['is_associated', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('foo', []);
            $this->createProduct('bar', ['groups' => ['groupA']]);
            $this->createProduct('baz', ['groups' => ['groupB']]);

            $fooBar = $this->createProduct('foo_bar', []);
            $fooBaz = $this->createProduct('foo_baz', []);
            $fooBarBaz = $this->createProduct('foo_bar_baz', []);
            $fooGroupA = $this->createProduct('foo_group_A', []);
            $fooGroupB = $this->createProduct('foo_group_B', []);
            $fooGroupsAB = $this->createProduct('foo_groups_AB', []);

            $this->updateProduct($fooBar, [
                'associations' => [
                    'PACK' => [
                        'groups'   => [],
                        'products' => ['bar'],
                    ],
                ],
            ]);

            $this->updateProduct($fooBaz, [
                'associations' => [
                    'PACK' => [
                        'groups'   => [],
                        'products' => ['baz'],
                    ],
                ],
            ]);

            $this->updateProduct($fooBarBaz, [
                'associations' => [
                    'SUBSTITUTION' => [
                        'groups'   => [],
                        'products' => ['bar', 'baz'],
                    ],
                ],
            ]);

            $this->updateProduct($fooGroupA, [
                'associations' => [
                    'UPSELL' => [
                        'groups'   => ['groupA'],
                        'products' => [],
                    ],
                ],
            ]);

            $this->updateProduct($fooGroupB, [
                'associations' => [
                    'UPSELL' => [
                        'groups'   => ['groupB'],
                        'products' => [],
                    ],
                ],
            ]);

            $this->updateProduct($fooGroupsAB, [
                'associations' => [
                    'X_SELL' => [
                        'groups'   => ['groupA', 'groupB'],
                        'products' => [],
                    ],
                ],
            ]);
        }
    }
}
