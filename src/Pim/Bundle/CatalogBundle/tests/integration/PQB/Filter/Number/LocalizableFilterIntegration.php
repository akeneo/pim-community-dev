<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Number;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createAttribute([
                'code'                => 'a_localizable_number',
                'type'                => AttributeTypes::NUMBER,
                'localizable'         => true,
                'scopable'            => false,
                'negative_allowed'    => true
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_localizable_number' => [
                        ['data' => -15, 'locale' => 'en_US', 'scope' => null],
                        ['data' => -14, 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_localizable_number' => [
                        ['data' => 19, 'locale' => 'en_US', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->execute([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_localizable_number', Operators::EQUALS, 15, ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_number', Operators::EQUALS, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_localizable_number', Operators::GREATER_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_number', Operators::GREATER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_number', Operators::GREATER_THAN, -14.0001, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->execute([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_localizable_number', Operators::IS_NOT_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_localizable_number', Operators::NOT_EQUAL, 15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_localizable_number', Operators::NOT_EQUAL, -15, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_number', Operators::NOT_EQUAL, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_number" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->execute([['a_localizable_number', Operators::NOT_EQUAL, 12]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_number" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_localizable_number', Operators::NOT_EQUAL, 12, ['locale' => 'NOT_FOUND']]]);
    }
}
