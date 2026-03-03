<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Number;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Number sorter integration tests
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
            'code'                => 'a_localizable_scopable_number',
            'type'                => AttributeTypes::NUMBER,
            'localizable'         => true,
            'scopable'            => true,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
        ]);

        $this->createProduct('product_one', [
            new SetNumberValue('a_localizable_scopable_number', 'ecommerce', 'en_US', '192.103'),
            new SetNumberValue('a_localizable_scopable_number', 'tablet', 'fr_FR', '-16'),
        ]);

        $this->createProduct('product_two', [
            new SetNumberValue('a_localizable_scopable_number', 'ecommerce', 'en_US', '-16'),
            new SetNumberValue('a_localizable_scopable_number', 'tablet', 'fr_FR', '192.103'),
        ]);

        $this->createProduct('product_three', [
            new SetNumberValue('a_localizable_scopable_number', 'tablet', 'de_DE', '52'),
        ]);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_number', Directions::ASCENDING, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three']);

        $result = $this->executeSorter([['a_localizable_scopable_number', Directions::ASCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_number', Directions::DESCENDING, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three']);

        $result = $this->executeSorter([['a_localizable_scopable_number', Directions::DESCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_localizable_scopable_number', 'A_BAD_DIRECTION', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
    }
}
