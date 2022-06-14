<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Date sorter integration tests for localizable and scopable attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([[
            'a_localizable_scopable_date',
            Directions::ASCENDING,
            ['locale' => 'fr_FR', 'scope' => 'tablet'],
        ]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product', 'product_four']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([[
            'a_localizable_scopable_date',
            Directions::DESCENDING,
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product', 'product_four']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([[
            'a_localizable_scopable_date',
            'A_BAD_DIRECTION',
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_date',
            'type'                => AttributeTypes::DATE,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createProduct('product_one', [
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'fr_FR', new \DateTime('2017-04-11'))
        ]);

        $this->createProduct('product_two', [
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'fr_FR', new \DateTime('2016-03-10'))
        ]);

        $this->createProduct('product_three', [
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'fr_FR', new \DateTime('2015-02-09'))
        ]);

        $this->createProduct('product_four', [
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'en_US', new \DateTime('2014-01-08'))
        ]);

        $this->createProduct('empty_product', []);
    }
}
