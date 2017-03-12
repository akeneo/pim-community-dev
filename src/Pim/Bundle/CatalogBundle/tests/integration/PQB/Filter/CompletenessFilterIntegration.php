<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilterIntegration extends AbstractFilterTestCase
{
    /**
     * +-----------------------------------------------+
     * |               |   Ecommerce   |     Tablet    |
     * |               | fr_FR | en_US | fr_FR | en_US |
     * +---------------+-------+-------+-------+-------+
     * | empty_product |   -   |  33%  |  25%  | 25%   |
     * | product_one   |   -   |  67%  |  50%  | 50%   |
     * | product_two   |   -   |  67%  |  75%  | 100%  |
     * +-----------------------------------------------+
     *
     * Note: completeness is not calculated for ecommerce-fr_FR because locale is not activated for this channel
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

    public function testOperatorInferior()
    {
        $result = $this->execute([['completeness', Operators::LOWER_THAN, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN, 100, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN, 100, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUAL_THAN, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUAL_THAN, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUAL_THAN, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorEqual()
    {
        $result = $this->execute([['completeness', Operators::EQUALS, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::EQUALS, 100, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::EQUALS, 100, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::EQUALS, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::EQUALS, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['completeness', Operators::EQUALS, 75, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::EQUALS, 25, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['completeness', Operators::EQUALS, 67, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['completeness', Operators::GREATER_THAN, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_THAN, 100, ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_THAN, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_THAN, 50, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUAL_THAN, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUAL_THAN, 100, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUAL_THAN, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUAL_THAN, 50, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['completeness', Operators::NOT_EQUAL, 100, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::NOT_EQUAL, 50, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorGreaterThanAllLocales()
    {
        $result = $this->execute([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);
    }

    public function testOperatorGreaterOrEqualThanAllLocales()
    {
        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 80, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['completeness', Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES, 0, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US', 'fr_FR']
        ]]]);
        $this->assert($result, []);
    }

    public function testOperatorLowerThanAllLocales()
    {
        $result = $this->execute([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 67, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 67, [
            'scope'   => 'ecommerce',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorLowerOrEqualThanAllLocales()
    {
        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 67, [
            'scope'   => 'ecommerce',
            'locales' => ['en_US']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 67, [
            'scope'   => 'ecommerce',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, []);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 50, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 100, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR', 'en_US']
        ]]]);
        $this->assert($result, ['product_one', 'empty_product']);

        $result = $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, [
            'scope'   => 'tablet',
            'locales' => ['fr_FR']
        ]]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testOperatorAll()
    {
        $result = $this->execute([['completeness', 'ALL', 0]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product', 'no_family']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects an array with the key "locales" as data.
     */
    public function testErrorLocalesIsMissing()
    {
        $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, ['scope' => 'ecommerce']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects an array of arrays as data.
     */
    public function testErrorLocalesIsMalformed()
    {
        $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75, ['scope' => 'ecommerce', 'locales' => 'string']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "completeness" expects a valid scope.
     */
    public function testErrorScopeIsMissing()
    {
        $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 75]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects a numeric as data, "string" given.
     */
    public function testErrorDataIsNotAnNumeric()
    {
        $this->execute([['completeness', Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES, 'string']]);
    }
}
