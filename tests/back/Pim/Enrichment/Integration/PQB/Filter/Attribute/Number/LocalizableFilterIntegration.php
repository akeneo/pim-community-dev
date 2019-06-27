<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Number;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_number',
            'type'                => AttributeTypes::NUMBER,
            'localizable'         => true,
            'scopable'            => false,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_number']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_number' => [
                    ['data' => -15, 'locale' => 'en_US', 'scope' => null],
                    ['data' => -14, 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_number' => [
                    ['data' => 19, 'locale' => 'en_US', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::EQUALS, 15, ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_number', Operators::EQUALS, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -14.0001, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::IS_NOT_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, -15, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_number" expects a locale, none given.');

        $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 12]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_number" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 12, ['locale' => 'NOT_FOUND']]]);
    }
}
