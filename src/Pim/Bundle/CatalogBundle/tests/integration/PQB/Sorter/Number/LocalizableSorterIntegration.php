<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Number;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Number sorter integration tests
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
            'code'             => 'a_localizable_number',
            'type'             => AttributeTypes::NUMBER,
            'localizable'      => true,
            'scopable'         => false,
            'negative_allowed' => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_number' => [
                    ['data' => '192.103', 'locale' => 'en_US', 'scope' => null],
                    ['data' => '-16', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_number' => [
                    ['data' => '-16', 'locale' => 'en_US', 'scope' => null],
                    ['data' => '192.103', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_localizable_number' => [
                    ['data' => '52', 'locale' => 'de_DE', 'scope' => null],
                ],
            ],
        ]);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_number', Directions::ASCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three']);

        $result = $this->executeSorter([['a_localizable_number', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_number', Directions::DESCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three']);

        $result = $this->executeSorter([['a_localizable_number', Directions::DESCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_number', 'A_BAD_DIRECTION', ['locale' => 'en_US']]]);
    }
}
