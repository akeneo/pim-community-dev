<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $group = $this->get('pim_catalog.factory.group')->create();
            $this->get('pim_catalog.updater.group')->update($group, [
                'code' => 'groupC',
                'type' => 'RELATED'
            ]);
            $this->get('pim_catalog.saver.group')->save($group);
        }
    }

    public function testOperatorIn()
    {
        $result = $this->execute([['groups', Operators::IN_LIST, ['groupC']]]);
        $this->assert($result, []);

        $result = $this->execute([['groups', Operators::IN_LIST, ['groupB', 'groupA']]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['groups', Operators::IN_LIST, ['groupA']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->execute([['groups', Operators::NOT_IN_LIST, ['groupA']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['groups', Operators::NOT_IN_LIST, ['groupB']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['groups', Operators::NOT_IN_LIST, ['groupC']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['groups', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['groups', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "groups" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['groups', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "groups" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['groups', Operators::BETWEEN, 'groupB']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }
}
