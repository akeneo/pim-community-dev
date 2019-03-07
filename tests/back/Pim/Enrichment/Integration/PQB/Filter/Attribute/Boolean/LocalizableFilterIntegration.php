<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Boolean;

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
            'code'                => 'a_localizable_yes_no',
            'type'                => AttributeTypes::BOOLEAN,
            'localizable'         => true,
            'scopable'            => false,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_yes_no' => [
                    ['data' => true, 'locale' => 'en_US', 'scope' => null],
                    ['data' => false, 'locale' => 'fr_FR', 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_yes_no' => [
                    ['data' => true, 'locale' => 'en_US', 'scope' => null],
                    ['data' => true, 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_yes_no', Operators::EQUALS, true, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_yes_no', Operators::EQUALS, true, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_yes_no', Operators::EQUALS, false, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true, ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_yes_no" expects a locale, none given.');
        $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_yes_no" expects an existing and activated locale, "NOT_FOUND" given.');
        $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true, ['locale' => 'NOT_FOUND']]]);
    }
}
