<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Boolean;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilterIntegration extends AbstractFilterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
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
        }
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_yes_no', Operators::EQUALS, true]]);
        $this->assert($result, ['yes']);

        $result = $this->execute([['a_yes_no', Operators::EQUALS, false]]);
        $this->assert($result, ['no']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_yes_no', Operators::NOT_EQUAL, true]]);
        $this->assert($result, ['no']);

        $result = $this->execute([['a_yes_no', Operators::NOT_EQUAL, false]]);
        $this->assert($result, ['yes']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_yes_no" expects a boolean as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['a_yes_no', Operators::NOT_EQUAL, 'string']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_yes_no" expects a boolean as data, "NULL" given.
     */
    public function testErrorDataIsNull()
    {
        $this->execute([['a_yes_no', Operators::NOT_EQUAL, null]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_yes_no" is not supported or does not support operator "CONTAINS"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['a_yes_no', Operators::CONTAINS, true]]);
    }
}
