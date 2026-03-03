<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSortDescendant()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::DESCENDING]],
            ['default_locale' => 'en_US', 'default_scope' => 'tablet']
        );
        $this->assertOrder($result, ['product_two', 'product_three', 'product_one', 'empty_product', 'no_family']);
    }

    public function testSortAscendant()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::ASCENDING]],
            ['default_locale' => 'en_US', 'default_scope' => 'tablet']
        );
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_three', 'product_two', 'no_family']);
    }

    public function testSortDescendantWithLocale()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::DESCENDING, ['locale' => 'fr_FR']]],
            ['default_locale' => 'en_US', 'default_scope' => 'tablet']
        );
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product', 'no_family']);
    }

    public function testSortAscendantWithLocale()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::ASCENDING, ['locale' => 'fr_FR']]],
            ['default_locale' => 'en_US', 'default_scope' => 'tablet']
        );
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_two', 'product_three', 'no_family']);
    }

    public function testSortDescendantWithChannel()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::DESCENDING, ['scope' => 'tablet']]],
            ['default_locale' => 'fr_FR', 'default_scope' => 'ecommerce']
        );
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product', 'no_family']);
    }

    public function testSortAscendantWithChannel()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::ASCENDING, ['scope' => 'tablet']]],
            ['default_locale' => 'fr_FR', 'default_scope' => 'ecommerce']
        );
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_two', 'product_three', 'no_family']);
    }

    public function testSortDescendantWithLocaleAndChannel()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::DESCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]],
            ['default_locale' => 'en_US', 'default_scope' => 'ecommerce']
        );
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product', 'no_family']);
    }

    public function testSortAscendantWithLocaleAndChannel()
    {
        $result = $this->executeSorter(
            [['completeness', Directions::ASCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]],
            ['default_locale' => 'en_US', 'default_scope' => 'ecommerce']
        );
        $this->assertOrder($result, ['empty_product', 'product_one', 'product_two', 'product_three', 'no_family']);
    }

    public function testWithLocaleNotBoundToChannel(): void
    {
        $result = $this->executeSorter(
            [
                // the first sort clause will have no effect as there is no completeness for the ecommerce/fr_FR couple
                ['completeness', Directions::ASCENDING, ['locale' => 'fr_FR', 'scope' => 'ecommerce']],
                // so only the second clause will be taken into account
                ['identifier', Directions::ASCENDING],
            ],
            ['default_locale' => 'fr_FR', 'default_scope' => 'ecommerce'],
        );
        $this->assertOrder($result, ['empty_product', 'no_family', 'product_one', 'product_three', 'product_two']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter(
            [['completeness', 'A_BAD_DIRECTION']],
            ['default_locale' => 'en_US', 'default_scope' => 'ecommerce']
        );
    }

    public function testErrorLocaleNotEmpty()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "locale" does not expect an empty value.');

        $this->executeSorter(
            [['completeness', Directions::ASCENDING]],
            ['default_scope' => 'ecommerce']
        );
    }

    public function testErrorScopeNotEmpty()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "scope" does not expect an empty value.');

        $this->executeSorter(
            [['completeness', Directions::ASCENDING]],
            ['default_locale' => 'en_US']
        );
    }

    /**
     * {@inheritdoc}
     *
     * +-------------------------------------------------------------------------+
     * |               |   Ecommerce   |     Tablet            | Ecommerce China |
     * |               | fr_FR | en_US | fr_FR | en_US | de_DE | en_US | zh_CN   |
     * +---------------+-------+-------+-------+---------------------------------+
     * | empty_product |   -   |  33%  |  25%  | 25%   |  25%  | 100%  | 100%    |
     * | product_one   |   -   |  66%  |  50%  | 50%   |  50%  | 100%  | 100%    |
     * | product_two   |   -   |  66%  |  100% | 100%  |  75%  | 100%  | 100%    |
     * | product_three |   -   |  66%  |  75%  | 75%   |  75%  | 100%  | 100%    |
     * | no_family     |   -   |  -    |   -   |   -   |   -   |   -   |   -     |
     * +-------------------------------------------------------------------------+
     *
     * Notes:
     *      - completeness is not calculated for ecommerce-fr_FR because locale is not activated for this channel
     *      - "Ecommerce China" is complete because this channel requires only the "sku"
     *      - completeness is not calculated on "no_family" has it has obviously no family
     */
    protected function setUp(): void
    {
        parent::setUp();

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code'                   => 'familyB',
            'attributes'             => [
                'sku',
                'a_metric',
                'a_localized_and_scopable_text_area',
                'a_scopable_price',
            ],
            'attribute_requirements' => [
                'tablet'    => ['sku', 'a_metric', 'a_localized_and_scopable_text_area', 'a_scopable_price'],
                'ecommerce' => ['sku', 'a_metric', 'a_scopable_price'],
            ],
        ]);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->createProduct('product_one', [
            new SetFamily('familyB'),
            new SetMeasurementValue('a_metric', null, null, 15, 'WATT')
        ]);

        $this->createProduct('product_two', [
            new SetFamily('familyB'),
            new SetMeasurementValue('a_metric', null, null, 15, 'WATT'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'text'),
            new SetPriceCollectionValue('a_scopable_price', 'tablet', null, [
                new PriceValue(15, 'EUR'),
                new PriceValue(15.5, 'USD'),
            ])
        ]);

        $this->createProduct('product_three', [
            new SetFamily('familyB'),
            new SetMeasurementValue('a_metric', null, null, 15, 'WATT'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'text'),
            new SetPriceCollectionValue('a_scopable_price', 'tablet', null, [
                new PriceValue(15, 'EUR'),
                new PriceValue(15.5, 'USD'),
            ])
        ]);

        $this->createProduct('empty_product', [new SetFamily('familyB')]);

        $this->createProduct('no_family', [
            new SetMeasurementValue('a_metric', null, null, 10, 'WATT'),
        ]);
    }
}
