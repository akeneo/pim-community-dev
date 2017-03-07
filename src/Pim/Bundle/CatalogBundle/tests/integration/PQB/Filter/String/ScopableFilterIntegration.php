<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\String;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createAttribute([
                'code'                => 'a_scopable_text',
                'type'                => AttributeTypes::TEXT,
                'localizable'         => false,
                'scopable'            => true,
            ]);

            $this->createProduct('cat', [
                'values' => [
                    'a_scopable_text' => [
                        ['data' => 'black cat', 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => 'cat', 'locale' => null, 'scope' => 'tablet'],
                    ]
                ]
            ]);

            $this->createProduct('cattle', [
                'values' => [
                    'a_scopable_text' => [
                        ['data' => 'cattle', 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => 'cattle', 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('dog', [
                'values' => [
                    'a_scopable_text' => [
                        ['data' => 'just a dog...', 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => 'dog', 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartsWith()
    {
        $result = $this->execute([['a_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_scopable_text', Operators::STARTS_WITH, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorEndsWith()
    {
        $result = $this->execute([['a_scopable_text', Operators::ENDS_WITH, 'ca', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_text', Operators::ENDS_WITH, 'ca', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_text', Operators::ENDS_WITH, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_scopable_text', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_scopable_text', Operators::CONTAINS, 'nope', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->execute([['a_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle', 'dog', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_text', Operators::IS_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->execute([['a_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_text" expects a scope, none given.
     */
    public function testErrorScopable()
    {
        $this->execute([['a_scopable_text', Operators::NOT_EQUAL, 'data']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_text" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_text', Operators::NOT_EQUAL, 'text', ['scope' => 'NOT_FOUND']]]);
    }
}
