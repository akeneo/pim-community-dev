<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Boolean;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_yes_no',
            'type'                => AttributeTypes::BOOLEAN,
            'localizable'         => true,
            'scopable'            => false,
        ]);

        $this->createProduct('product_one', [
            new SetBooleanValue('a_localizable_yes_no', null, 'en_US', true),
            new SetBooleanValue('a_localizable_yes_no', null, 'fr_FR', false),
        ]);

        $this->createProduct('product_two', [
            new SetBooleanValue('a_localizable_yes_no', null, 'en_US', false),
            new SetBooleanValue('a_localizable_yes_no', null, 'fr_FR', true),
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

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_localizable_yes_no', 'A_BAD_DIRECTION', ['locale' => 'fr_FR']]]);
    }
}
