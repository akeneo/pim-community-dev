<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Boolean;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('yes', [
            'values' => [
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('no', [
            'values' => [
                'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('empty', []);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_yes_no', Operators::EQUALS, true]]);
        $this->assert($result, ['yes']);

        $result = $this->executeFilter([['a_yes_no', Operators::EQUALS, false]]);
        $this->assert($result, ['no']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, true]]);
        $this->assert($result, ['no']);

        $result = $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, false]]);
        $this->assert($result, ['yes']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_yes_no', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['yes', 'no']);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_yes_no" expects a boolean as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, 'string']]);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_yes_no" expects a boolean as data, "NULL" given.
     */
    public function testErrorDataIsNull()
    {
        $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, null]]);
    }

    /**
     * @expectedException \Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_yes_no" is not supported or does not support operator "CONTAINS"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_yes_no', Operators::CONTAINS, true]]);
    }
}
