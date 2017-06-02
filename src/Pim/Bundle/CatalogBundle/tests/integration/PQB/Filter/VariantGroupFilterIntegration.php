<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantGroupFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testOperatorIn()
    {
        $result = $this->executeFilter([['variant_group', Operators::IN_LIST, ['variantC']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['variant_group', Operators::IN_LIST, ['variantB']]]);
        $this->assert($result, ['bar']);

        $result = $this->executeFilter([['variant_group', Operators::IN_LIST, ['variantA', 'variantB']]]);
        $this->assert($result, ['foo', 'bar']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['variant_group', Operators::NOT_IN_LIST, ['variantC']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);

        $result = $this->executeFilter([['variant_group', Operators::NOT_IN_LIST, ['variantB']]]);
        $this->assert($result, ['foo', 'baz']);

        $result = $this->executeFilter([['variant_group', Operators::NOT_IN_LIST, ['variantA']]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['variant_group', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['baz']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['variant_group', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['foo', 'bar']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "variant_group" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['variant_group', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "variant_group" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupportedForVariantGroups()
    {
        $this->executeFilter([['variant_group', Operators::BETWEEN, 'groupB']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $group = $this->get('pim_catalog.factory.group')->createGroup('VARIANT');
        $this->get('pim_catalog.updater.group')->update(
            $group,
            [
                'code' => 'variantC',
            ]
        );
        $this->get('pim_catalog.saver.group')->save($group);

        $this->createProduct('foo', ['groups' => ['groupA', 'groupB'], 'variant_group' => 'variantA']);
        $this->createProduct('bar', ['variant_group' => 'variantB']);
        $this->createProduct('baz', []);
    }
}
