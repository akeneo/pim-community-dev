<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilterIntegration extends AbstractFilterTestCase
{
    public function testOperatorIn()
    {
        $result = $this->execute([['categories', Operators::IN_LIST, ['master']]]);
        $this->assert($result, []);

        $result = $this->execute([['categories', Operators::IN_LIST, ['categoryA1', 'categoryA2']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->execute([['categories', Operators::NOT_IN_LIST, ['master']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);

        $result = $this->execute([['categories', Operators::NOT_IN_LIST, ['categoryA1', 'categoryA2']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorUnclassified()
    {
        $result = $this->execute([['categories', Operators::UNCLASSIFIED, []]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorInOrUnclassified()
    {
        $result = $this->execute([['categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['categoryB']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);

        $result = $this->execute([['categories', Operators::IN_LIST_OR_UNCLASSIFIED, ['master']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorInChildren()
    {
        $result = $this->execute([['categories', Operators::IN_CHILDREN_LIST, ['master']]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['categories', Operators::IN_CHILDREN_LIST, ['categoryA1']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotInChildren()
    {
        $result = $this->execute([['categories', Operators::NOT_IN_CHILDREN_LIST, ['master']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "categories" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['categories', Operators::GREATER_OR_EQUAL_THAN, ['categoryA1']]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalSqlCatalogPath()]);
    }
}
