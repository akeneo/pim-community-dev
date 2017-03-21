<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, ['code' => 'familyB']);
            $this->get('pim_catalog.saver.family')->save($family);
        }
    }

    public function testOperatorIn()
    {
        $result = $this->execute([['family', Operators::IN_LIST, ['familyB']]]);
        $this->assert($result, []);

        $result = $this->execute([['family', Operators::IN_LIST, ['familyB', 'familyA']]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['family', Operators::IN_LIST, ['familyA']]]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->execute([['family', Operators::NOT_IN_LIST, ['familyA']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['family', Operators::NOT_IN_LIST, ['familyB']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['family', Operators::IS_EMPTY, '']]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['family', Operators::IS_NOT_EMPTY, '']]);
        $this->assert($result, ['foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "family" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['family', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "family" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['family', Operators::BETWEEN, 'familyA']]);
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
