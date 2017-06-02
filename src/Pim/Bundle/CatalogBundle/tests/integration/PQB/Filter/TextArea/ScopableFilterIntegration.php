<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_scopable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createProduct('cat', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'black cat', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'cat', 'locale' => null, 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'cattle', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'cattle', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_scopable_text_area' => [
                    ['data' => 'just a dog...', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'dog', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::STARTS_WITH, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::CONTAINS, 'nope', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::IS_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_text_area" expects a scope, none given.
     */
    public function testErrorScopable()
    {
        $this->executeFilter([['a_scopable_text_area', Operators::NOT_EQUAL, 'data']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_text_area" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->executeFilter([['a_scopable_text_area', Operators::NOT_EQUAL, 'text', ['scope' => 'NOT_FOUND']]]);
    }
}
