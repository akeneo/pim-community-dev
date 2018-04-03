<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Date;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Date sorter integration tests for localizable attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_date', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_date', Directions::DESCENDING,  ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_date', 'A_BAD_DIRECTION', ['locale' => 'fr_FR']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_date', Directions::DESCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_date', Directions::ASCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_date',
            'type'                => AttributeTypes::DATE,
            'localizable'         => true,
            'scopable'            => false,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_date' => [
                    ['data' => '2017-04-11', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_date' => [
                    ['data' => '2016-03-10', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_localizable_date' => [
                    ['data' => '2015-02-09', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }
}
