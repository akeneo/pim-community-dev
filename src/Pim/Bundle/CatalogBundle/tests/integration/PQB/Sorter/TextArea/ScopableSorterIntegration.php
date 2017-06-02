<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Text area sorter integration tests for scopable attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_scopable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createProduct('cat', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'black cat', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'red cat', 'locale' => null, 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'cattle', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'cattle', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'just a dog...', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'dog', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_scopable_text_area', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_text_area', Directions::ASCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['cattle', 'dog', 'cat', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_scopable_text_area', Directions::DESCENDING,  ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['dog', 'cattle', 'cat', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_text_area', Directions::DESCENDING,  ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['cat', 'dog', 'cattle', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_scopable_text_area', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }
}
