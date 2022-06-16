<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Options;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

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
    protected function setUp(): void
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

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_multi_select']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, ['orange']),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, ['black', 'purple']),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
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
        $this->assert($result, ['empty_product', 'product_one']);

        $result = $this->executeFilter([['a_multi_select', Operators::NOT_IN_LIST, ['orange']]]);
        $this->assert($result, ['empty_product', 'product_two']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_multi_select" expects an array as data, "string" given.');

        $this->executeFilter([['a_multi_select', Operators::IN_LIST, 'string']]);
    }

    public function testErrorOptionNotFound()
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage('Object "options" with code "NOT_FOUND" does not exist');

        $this->executeFilter([['a_multi_select', Operators::IN_LIST, ['NOT_FOUND']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_multi_select" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['a_multi_select', Operators::BETWEEN, ['orange', 'black']]]);
    }
}
