<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Media;

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
                'code'                => 'a_localizable_media',
                'type'                => AttributeTypes::IMAGE,
                'localizable'         => false,
                'scopable'            => true
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_image' => [
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'scope' => 'ecommerce', 'locale' => null],
                        ['data' => $this->getFixturePath('ziggy.png'), 'scope' => 'tablet', 'locale' => null],
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_image' => [
                        ['data' => $this->getFixturePath('ziggy.png'), 'scope' => 'ecommerce', 'locale' => null],
                        ['data' => $this->getFixturePath('ziggy.png'), 'scope' => 'tablet', 'locale' => null],
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartWith()
    {
        $result = $this->execute([['a_scopable_image', Operators::STARTS_WITH, 'aken', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_image', Operators::STARTS_WITH, 'aken', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorEndWith()
    {
        $result = $this->execute([['a_scopable_image', Operators::ENDS_WITH, 'ziggy.png', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_image', Operators::ENDS_WITH, 'ziggy.png', ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_image', Operators::ENDS_WITH, 'ziggy', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_scopable_image', Operators::CONTAINS, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_image', Operators::CONTAINS, 'ziggy', ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_image', Operators::CONTAINS, 'igg', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'other', ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_image', Operators::EQUALS, 'ziggy.png', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_image', Operators::EQUALS, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_image', Operators::IS_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_image', Operators::IS_NOT_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_image', Operators::NOT_EQUAL, 'akene', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_image" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_image', Operators::NOT_EQUAL, '2016-09-23', ['scope' => 'NOT_FOUND']]]);
    }
}
