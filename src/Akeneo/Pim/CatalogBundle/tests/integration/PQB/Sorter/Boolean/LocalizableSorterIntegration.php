<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Boolean;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Boolean sorter integration tests for localizable attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_yes_no',
            'type'                => AttributeTypes::BOOLEAN,
            'localizable'         => true,
            'scopable'            => false,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_yes_no' => [
                    ['data' => true, 'locale' => 'en_US', 'scope' => null],
                    ['data' => false, 'locale' => 'fr_FR', 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_yes_no' => [
                    ['data' => false, 'locale' => 'en_US', 'scope' => null],
                    ['data' => true, 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_yes_no', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_yes_no', Directions::ASCENDING,  ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_yes_no', Directions::DESCENDING,  ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_yes_no', Directions::DESCENDING,  ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_yes_no', 'A_BAD_DIRECTION', ['locale' => 'fr_FR']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_yes_no', Directions::DESCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_yes_no', Directions::ASCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }
}
