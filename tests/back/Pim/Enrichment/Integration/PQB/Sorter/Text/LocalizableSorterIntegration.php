<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Text;

use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Localizable text attribute sorter integration tests
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
            'code'        => 'a_localizable_text',
            'type'        => AttributeTypes::TEXT,
            'localizable' => true,
            'scopable'    => false,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_text' => [
                    ['data' => 'cat is beautiful', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'dog is wonderful', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_text' => [
                    ['data' => 'dog is wonderful', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'cat is beautiful', 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_text', Directions::ASCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_text', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_text',
                Directions::DESCENDING,
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_text',
                Directions::DESCENDING,
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_text', 'A_BAD_DIRECTION', ['locale' => 'en_US']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_text', Directions::DESCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_text', Directions::ASCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }
}
