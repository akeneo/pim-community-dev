<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Option;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Scopable and localizable attribute option sorter integration tests (simple select)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'        => 'a_localizable_scopable_simple_select',
            'type'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => true,
            'scopable'    => true,
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_scopable_simple_select',
            'code'      => 'orange',
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_scopable_simple_select',
            'code'      => 'black',
        ]);

        $this->createProduct('product_one', [
            new SetSimpleSelectValue('a_localizable_scopable_simple_select', 'ecommerce', 'en_US', 'orange'),
            new SetSimpleSelectValue('a_localizable_scopable_simple_select', 'tablet', 'fr_FR', 'black'),
        ]);

        $this->createProduct('product_two', [
            new SetSimpleSelectValue('a_localizable_scopable_simple_select', 'ecommerce', 'en_US', 'black'),
            new SetSimpleSelectValue('a_localizable_scopable_simple_select', 'tablet', 'fr_FR', 'orange'),
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_scopable_simple_select',
                Directions::ASCENDING,
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_scopable_simple_select',
                Directions::ASCENDING,
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([
            [
                'a_localizable_scopable_simple_select',
                Directions::DESCENDING,
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([
            [
                'a_localizable_scopable_simple_select',
                Directions::DESCENDING,
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([
            [
                'a_localizable_scopable_simple_select',
                'A_BAD_DIRECTION',
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
    }
}
