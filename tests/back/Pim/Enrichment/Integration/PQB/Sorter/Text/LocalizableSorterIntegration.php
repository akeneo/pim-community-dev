<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Text;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'        => 'a_localizable_text',
            'type'        => AttributeTypes::TEXT,
            'localizable' => true,
            'scopable'    => false,
        ]);

        $this->createProduct('product_one', [
            new SetTextValue('a_localizable_text', null, 'en_US', 'cat is beautiful'),
            new SetTextValue('a_localizable_text', null, 'fr_FR', 'dog is wonderful'),
        ]);

        $this->createProduct('product_two', [
            new SetTextValue('a_localizable_text', null, 'en_US', 'dog is wonderful'),
            new SetTextValue('a_localizable_text', null, 'fr_FR', 'cat is beautiful'),
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

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_localizable_text', 'A_BAD_DIRECTION', ['locale' => 'en_US']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_localizable_text', Directions::DESCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_two']);

        $result = $this->executeSorter([['a_localizable_text', Directions::ASCENDING, ['locale' => 'de_DE']]]);
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_two']);
    }
}
