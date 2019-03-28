<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

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
class FamilyFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, ['code' => 'familyB']);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->createProduct('foo', ['family' => 'familyA']);
        $this->createProduct('bar', []);
        $this->createProduct('baz', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['family', Operators::IN_LIST, ['familyB']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['family', Operators::IN_LIST, ['familyB', 'familyA']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['family', Operators::IN_LIST, ['familyA']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['family', Operators::NOT_IN_LIST, ['familyA']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['family', Operators::NOT_IN_LIST, ['familyB']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['family', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['family', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['foo']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "family" expects an array as data, "string" given.');

        $this->executeFilter([['family', Operators::IN_LIST, 'string']]);
    }

    public function testErrorValueNotFound()
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage('Object "family" with code "UNKNOWN_FAMILY" does not exist');

        $this->executeFilter([['family', Operators::IN_LIST, ['UNKNOWN_FAMILY']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "family" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['family', Operators::BETWEEN, 'familyA']]);
    }
}
