<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Option;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttributeOption([
           'attribute' => 'a_simple_select',
           'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_simple_select',
            'code'      => 'black'
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_simple_select']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_simple_select' => [
                    ['data' => 'orange', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_simple_select' => [
                    ['data' => 'black', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_simple_select', Operators::IN_LIST, ['orange']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_simple_select', Operators::IN_LIST, ['orange', 'black']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_simple_select', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_simple_select', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_simple_select', Operators::NOT_IN_LIST, ['black']]]);
        $this->assert($result, ['empty_product','product_one']);

        $result = $this->executeFilter([['a_simple_select', Operators::NOT_IN_LIST, ['orange']]]);
        $this->assert($result, ['empty_product','product_two']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_simple_select" expects an array as data, "string" given.');

        $this->executeFilter([['a_simple_select', Operators::IN_LIST, 'string']]);
    }

    public function testErrorOptionNotFound()
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage('Object "option" with code "NOT_FOUND" does not exist');

        $this->executeFilter([['a_simple_select', Operators::IN_LIST, ['NOT_FOUND']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_simple_select" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['a_simple_select', Operators::BETWEEN, ['NOT_FOUND']]]);
    }
}
