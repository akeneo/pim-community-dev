<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('foo', ['categories' => ['categoryA1', 'categoryB']]);
        $this->createProduct('bar', []);
        $this->createProduct('baz', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['master']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['categoryA1', 'categoryA2']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['categories', Operators::NOT_IN_LIST, ['master']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);

        $result = $this->executeFilter([['categories', Operators::NOT_IN_LIST, ['categoryA1', 'categoryA2']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorUnclassified()
    {
        $result = $this->executeFilter([['categories', Operators::UNCLASSIFIED, []]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorInOrUnclassified()
    {
        $result = $this->executeFilter([['categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['categoryB']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);

        $result = $this->executeFilter([['categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['master']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorInChildren()
    {
        $result = $this->executeFilter([['categories', Operators::IN_CHILDREN_LIST, ['master']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['categories', Operators::IN_CHILDREN_LIST, ['categoryA1']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotInChildren()
    {
        $result = $this->executeFilter([['categories', Operators::NOT_IN_CHILDREN_LIST, ['master']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "categories" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['categories', Operators::GREATER_OR_EQUAL_THAN, ['categoryA1']]]);
    }
}
