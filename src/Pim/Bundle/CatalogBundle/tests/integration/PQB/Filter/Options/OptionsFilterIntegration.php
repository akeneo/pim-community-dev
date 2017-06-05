<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Options;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttributeOption([
           'attribute' => 'a_multi_select',
           'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_multi_select',
            'code'      => 'black'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_multi_select',
            'code'      => 'purple'
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_multi_select' => [
                    ['data' => ['orange'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_multi_select' => [
                    ['data' => ['black', 'purple'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_multi_select', Operators::IN_LIST, ['orange']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_multi_select', Operators::IN_LIST, ['orange', 'black']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_multi_select', Operators::IN_LIST, ['purple']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_multi_select', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_multi_select', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_multi_select', Operators::NOT_IN_LIST, ['black']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_multi_select', Operators::NOT_IN_LIST, ['orange']]]);
        $this->assert($result, ['product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_multi_select" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_multi_select', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Object "options" with code "NOT_FOUND" does not exist
     */
    public function testErrorOptionNotFound()
    {
        $this->executeFilter([['a_multi_select', Operators::IN_LIST, ['NOT_FOUND']]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_multi_select" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_multi_select', Operators::BETWEEN, ['orange', 'black']]]);
    }
}
