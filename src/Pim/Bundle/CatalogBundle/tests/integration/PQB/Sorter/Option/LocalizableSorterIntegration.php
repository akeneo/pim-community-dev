<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Option;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Localizable option attribute sorter integration tests (simple select)
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
            'code'        => 'a_localizable_simple_select',
            'type'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => true,
            'scopable'    => false,
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_simple_select',
            'code'      => 'orange',
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_simple_select',
            'code'      => 'black',
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_simple_select' => [
                    ['data' => 'black', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'orange', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_simple_select' => [
                    ['data' => 'orange', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'black', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_simple_select', Directions::ASCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_simple_select', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_simple_select',
                Directions::DESCENDING,
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_simple_select',
                Directions::DESCENDING,
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_simple_select', 'A_BAD_DIRECTION', ['locale' => 'en_US']]]);
    }
}
