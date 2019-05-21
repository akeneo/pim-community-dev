<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Media;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_media',
            'type'                => AttributeTypes::IMAGE,
            'localizable'         => false,
            'scopable'            => true
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png')), 'scope' => 'tablet', 'locale' => null],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png')), 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png')), 'scope' => 'tablet', 'locale' => null],
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartWith()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::STARTS_WITH, 'aken', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_scopable_image', Operators::STARTS_WITH, 'aken', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::CONTAINS, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_scopable_image', Operators::CONTAINS, 'ziggy', ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_scopable_image', Operators::CONTAINS, 'igg', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_image', Operators::DOES_NOT_CONTAIN, 'other', ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::EQUALS, 'ziggy.png', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_scopable_image', Operators::EQUALS, 'ziggy', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::IS_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::IS_NOT_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_scopable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_scopable_image', Operators::NOT_EQUAL, 'akene', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_image" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_image', Operators::NOT_EQUAL, '2016-09-23', ['scope' => 'NOT_FOUND']]]);
    }
}
