<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     *
     * +-------------------------------------------------------------------------+
     * |               |   Ecommerce   |     Tablet            | Ecommerce China |
     * |               | fr_FR | en_US | fr_FR | en_US | de_DE | en_US | zh_CN   |
     * +---------------+-------+-------+-------+---------------------------------+
     * | empty_product |   -   |  33%  |  25%  | 25%   |  25%  | 100%  | 100%    |
     * | product_one   |   -   |  66%  |  50%  | 50%   |  50%  | 100%  | 100%    |
     * | product_two   |   -   |  66%  |  75%  | 100%  |  75%  | 100%  | 100%    |
     * | no_family     |   -   |  -    |   -   |   -   |   -   |   -   |   -     |
     * +-------------------------------------------------------------------------+
     *
     * Notes:
     *      - =, >, >=, < and <= operators checks the completeness on all the locales of a channel (we talked about this
     *        expected behavior with Delphine)
     *      - completeness is not calculated for ecommerce-fr_FR because locale is not activated for this channel
     *      - "Ecommerce China" are complete because this channel requires only the "sku"
     *      - completeness is not calculated on "no_family" has it has obviously no family
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, [
                'code'                   => 'familyB',
                'attributes'             => ['sku', 'a_metric', 'a_localized_and_scopable_text_area', 'a_scopable_price'],
                'attribute_requirements' => [
                    'tablet'    => ['sku', 'a_metric', 'a_localized_and_scopable_text_area', 'a_scopable_price'],
                    'ecommerce' => ['sku', 'a_metric', 'a_scopable_price'],
                ]
            ]);
            $this->get('pim_catalog.saver.family')->save($family);

            $this->createProduct('product_one', [
                'family' => 'familyB',
                'values' => [
                    'a_metric' => [['data' => ['amount' => 15, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('product_two', [
                'family' => 'familyB',
                'values' => [
                    'a_metric'                           => [['data' => ['amount' => 15, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'tablet']],
                    'a_scopable_price'                   => [
                        [
                            'data'      => [
                                ['amount' => 15, 'currency' => 'EUR'],
                                ['amount' => 15.5, 'currency' => 'USD']
                            ], 'locale' => null, 'scope' => 'tablet'
                        ]
                    ],
                ]
            ]);

            $this->createProduct('empty_product', [
                'family' => 'familyB',
            ]);

            $this->createProduct('no_family', [
                'values' => [
                    'a_metric' => [['data' => ['amount' => 10, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]]
                ]
            ]);
        }
    }

    public function testOperatorIsEmpty()
    {
        $result = $this->executeFilter([['completeness', Operators::IS_EMPTY, null]]);
        $this->assert($result, ['no_family']);
    }

    public function testOperatorLowerThan()
    {
        $this->doTestOperatorInferior(Operators::LOWER_THAN);
    }

    public function testOperatorLowerThanOnAtLeastOneLocale()
    {
        $this->doTestOperatorInferior(Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorInferior($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorLowerOrEqualThan()
    {
        $this->doTestOperatorInferiorOrEqual(Operators::LOWER_OR_EQUAL_THAN);
    }

    public function testOperatorLowerOrEqualThanOnAtLeastOneLocale()
    {
        $this->doTestOperatorInferiorOrEqual(Operators::LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorInferiorOrEqual($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $this->doTestOperatorSame(Operators::EQUALS);
    }

    public function testOperatorEqualsOnAtLeastOneLocale()
    {
        $this->doTestOperatorSame(Operators::EQUALS_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorSame($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['completeness', $operator, 75, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', $operator, 25, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 66, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorGreaterThan()
    {
        $this->doTestOperatorSuperior(Operators::GREATER_THAN);
    }

    public function testOperatorGreaterThanOnAtLeastOneLocale()
    {
        $this->doTestOperatorSuperior(Operators::GREATER_THAN_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorSuperior($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorGreaterOrEqualThan()
    {
        $this->doTestOperatorSuperiorOrEqual(Operators::GREATER_OR_EQUAL_THAN);
    }

    public function testOperatorGreaterOrEqualThanOnAtLeastOneLocale()
    {
        $this->doTestOperatorSuperiorOrEqual(Operators::GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorSuperiorOrEqual($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotEqual()
    {
        $this->doTestOperatorDifferent(Operators::NOT_EQUAL);
    }

    public function testOperatorNotEqualOnAtLeastOneLocale()
    {
        $this->doTestOperatorDifferent(Operators::NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE);
    }

    private function doTestOperatorDifferent($operator)
    {
        $result = $this->executeFilter([['completeness', $operator, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 75, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeFilter([['completeness', $operator, 33, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two', 'product_one']);
    }

    public function testOperatorGreaterThanAllLocales()
    {
        $result = $this->executeFilter([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);
    }

    public function testOperatorGreaterOrEqualThanAllLocales()
    {
        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 0, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);
    }

    public function testOperatorLowerThanAllLocales()
    {
        $result = $this->executeFilter([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 66, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 66, [
            'scope'   => 'ecommerce',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorLowerOrEqualThanAllLocales()
    {
        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 66, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 66, [
            'scope'   => 'ecommerce',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 100, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testOperatorAll()
    {
        $result = $this->executeFilter([['completeness', 'ALL', 0]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product', 'no_family']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects an array with the key "locales" as data.
     */
    public function testErrorLocalesIsMissing()
    {
        $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, ['scope' => 'ecommerce']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects an array of arrays as data.
     */
    public function testErrorLocalesIsMalformed()
    {
        $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, ['scope' => 'ecommerce', 'locales' => 'string']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "completeness" expects a valid scope.
     */
    public function testErrorScopeIsMissing()
    {
        $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects a numeric as data, "string" given.
     */
    public function testErrorDataIsNotAnNumeric()
    {
        $this->executeFilter([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 'string']]);
    }
}
