<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Metric;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Metric sorter integration tests for localizable and scopable attribute
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
            'code'                => 'a_localizable_scopable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => true,
            'scopable'            => true,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
            'metric_family'       => 'Power',
            'default_metric_unit' => 'WATT'
        ]);

        $this->createProduct('product_one', [
            new SetMeasurementValue('a_localizable_scopable_metric', 'tablet', 'fr_FR', '10.5', 'KILOWATT')
        ]);

        $this->createProduct('product_two', [
            new SetMeasurementValue('a_localizable_scopable_metric', 'tablet', 'fr_FR', '15000', 'KILOWATT')
        ]);

        $this->createProduct('product_three', [
            new SetMeasurementValue('a_localizable_scopable_metric', 'tablet', 'fr_FR', '-2.5', 'KILOWATT')
        ]);

        $this->createProduct('product_four', [
            new SetMeasurementValue('a_localizable_scopable_metric', 'tablet', 'en_US', '12', 'KILOWATT')
        ]);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_metric', Directions::ASCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_three', 'product_one', 'product_two', 'product_four']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_metric', Directions::DESCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three', 'product_four']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_localizable_scopable_metric', 'A_BAD_DIRECTION', ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
    }
}
