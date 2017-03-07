<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Boolean;

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
                'code'                => 'a_scopable_yes_no',
                'type'                => AttributeTypes::BOOLEAN,
                'localizable'         => false,
                'scopable'            => true,
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_yes_no' => [
                        ['data' => true, 'scope' => 'ecommerce', 'locale' => null],
                        ['data' => false, 'scope' => 'tablet', 'locale' => null]
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_yes_no' => [
                        ['data' => true, 'scope' => 'ecommerce', 'locale' => null],
                        ['data' => true, 'scope' => 'tablet', 'locale' => null],
                    ]
                ]
            ]);
        }
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_yes_no', Operators::EQUALS, true, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_yes_no', Operators::EQUALS, false, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_yes_no', Operators::EQUALS, true, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_yes_no" expects a scope, none given.
     */
    public function testErrorScopable()
    {
        $this->execute([['a_scopable_yes_no', Operators::NOT_EQUAL, true]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_yes_no" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'NOT_FOUND']]]);
    }
}
