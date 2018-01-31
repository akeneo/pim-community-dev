<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $group = $this->get('pim_catalog.factory.group')->create();
        $this->get('pim_catalog.updater.group')->update($group, [
            'code' => 'groupC',
            'type' => 'RELATED'
        ]);
        $this->get('pim_catalog.saver.group')->save($group);

        $this->createProduct('foo', ['groups' => ['groupA', 'groupB']]);
        $this->createProduct('bar', []);
        $this->createProduct('baz', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['groups', Operators::IN_LIST, ['groupC']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['groups', Operators::IN_LIST, ['groupB', 'groupA']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['groups', Operators::IN_LIST, ['groupA']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['groups', Operators::NOT_IN_LIST, ['groupA']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['groups', Operators::NOT_IN_LIST, ['groupB']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['groups', Operators::NOT_IN_LIST, ['groupC']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['groups', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['groups', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "groups" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['groups', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "groups" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupportedForGroups()
    {
        $this->executeFilter([['groups', Operators::BETWEEN, 'groupB']]);
    }
}
