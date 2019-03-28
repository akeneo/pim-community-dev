<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\TextArea;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

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
    protected function setUp(): void
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

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_scopable_text_area', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_scopable_text_area', Directions::DESCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_text_area', Directions::ASCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);
    }
}
