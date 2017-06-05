<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Boolean;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

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
    protected function setUp()
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

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_yes_no" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_yes_no" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([['a_localizable_yes_no', Operators::NOT_EQUAL, true, ['locale' => 'NOT_FOUND']]]);
    }
}
