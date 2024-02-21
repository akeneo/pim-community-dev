<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Boolean;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);

        $this->createProduct('no', [
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $this->createProduct('empty', [new SetFamily('familyA')]);
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

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_yes_no', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['empty']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_yes_no" expects a boolean as data, "string" given.');
        $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, 'string']]);
    }

    public function testErrorDataIsNull()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_yes_no" expects a boolean as data, "NULL" given.');
        $this->executeFilter([['a_yes_no', Operators::NOT_EQUAL, null]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_yes_no" is not supported or does not support operator "CONTAINS"');
        $this->executeFilter([['a_yes_no', Operators::CONTAINS, true]]);
    }
}
