<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StatusFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->resetIndex();

            $this->createProduct('foo', ['enabled' => true]);
            $this->createProduct('bar', ['enabled' => false]);
        }
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['enabled', Operators::EQUALS, true]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['enabled', Operators::EQUALS, false]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorNotEqual()
    {
        $result = $this->execute([['enabled', Operators::NOT_EQUAL, true]]);
        $this->assert($result, ['bar']);

        $result = $this->execute([['enabled', Operators::NOT_EQUAL, false]]);
        $this->assert($result, ['foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "enabled" expects a boolean as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['enabled', Operators::EQUALS, 'string']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "enabled" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['enabled', Operators::BETWEEN, false]]);
    }
}
