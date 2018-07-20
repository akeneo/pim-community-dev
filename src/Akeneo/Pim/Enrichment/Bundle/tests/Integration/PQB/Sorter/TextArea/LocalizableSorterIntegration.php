<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Text area sorter integration tests for localizable attribute
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
            'code'        => 'a_localizable_text_area',
            'type'        => AttributeTypes::TEXTAREA,
            'localizable' => true,
            'scopable'    => false,
        ]);

        $this->createProduct('cat', [
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'black cat', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'chat <b>noir</b>', 'locale' => 'fr_FR', 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'cattle', 'locale' => 'en_US', 'scope' => null],
                    ['data' => '<h1>cattle</h1>', 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'just a dog...', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'juste un chien', 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_text_area', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['cattle', 'cat', 'dog', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_text_area', Directions::ASCENDING, ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_text_area', Directions::DESCENDING,  ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['dog', 'cat', 'cattle', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_text_area', Directions::DESCENDING,  ['locale' => 'en_US']]]);
        $this->assertOrder($result, ['dog', 'cattle', 'cat', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_text_area', 'A_BAD_DIRECTION', ['locale' => 'fr_FR']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_text_area', Directions::DESCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_text_area', Directions::ASCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);
    }
}
