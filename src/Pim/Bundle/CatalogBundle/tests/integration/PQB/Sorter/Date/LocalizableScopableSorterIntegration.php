<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Date;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Date sorter integration tests for localizable and scopable attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([[
            'a_localizable_scopable_date',
            Directions::ASCENDING,
            ['locale' => 'fr_FR', 'scope' => 'tablet'],
        ]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'product_four', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([[
            'a_localizable_scopable_date',
            Directions::DESCENDING,
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'product_four', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([[
            'a_localizable_scopable_date',
            'A_BAD_DIRECTION',
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_date',
            'type'                => AttributeTypes::DATE,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2017-04-11', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2016-03-10', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2015-02-09', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_four', [
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2014-01-08', 'locale' => 'en_US', 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }
}
