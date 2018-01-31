<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Media;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_scopable_image' => [
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_scopable_image' => [
                    ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartWith()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::CONTAINS, 'igg', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::EQUALS, 'ziggy.png', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::EQUALS, 'ziggy', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::IS_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akene', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_image" expects a locale, none given.
     */
    public function testErrorLocale()
    {
        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, '2016-09-23']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_image" expects a scope, none given.
     */
    public function testErrorScope()
    {
        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'fr_FR']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_image" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_image" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->executeFilter([['a_localizable_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'fr_FR', 'scope' => 'NOT_FOUND']]]);
    }
}
