<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StatusFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('foo', ['enabled' => true]);
        $this->createProduct('bar', ['enabled' => false]);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['enabled', Operators::EQUALS, true]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['enabled', Operators::EQUALS, false]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorNotEqual()
    {
        $result = $this->executeFilter([['enabled', Operators::NOT_EQUAL, true]]);
        $this->assert($result, ['bar']);

        $result = $this->executeFilter([['enabled', Operators::NOT_EQUAL, false]]);
        $this->assert($result, ['foo']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "enabled" expects a boolean as data, "string" given.');

        $this->executeFilter([['enabled', Operators::EQUALS, 'string']]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "enabled" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['enabled', Operators::BETWEEN, false]]);
    }
}
