<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\String;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('cat', [
                'values' => [
                    'a_text' => [['data' => 'cat', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('cattle', [
                'values' => [
                    'a_text' => [['data' => 'cattle', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('dog', [
                'values' => [
                    'a_text' => [['data' => 'dog', 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartsWith()
    {
        $result = $this->execute([['a_text', Operators::STARTS_WITH, 'at']]);
        $this->assert($result, []);

        $result = $this->execute([['a_text', Operators::STARTS_WITH, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text', Operators::STARTS_WITH, 'cats']]);
        $this->assert($result, []);
    }

    public function testOperatorEndsWith()
    {
        $result = $this->execute([['a_text', Operators::ENDS_WITH, 'ca']]);
        $this->assert($result, []);

        $result = $this->execute([['a_text', Operators::ENDS_WITH, 'g']]);
        $this->assert($result, ['dog']);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_text', Operators::CONTAINS, 'at']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text', Operators::CONTAINS, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_text', Operators::DOES_NOT_CONTAIN, 'at']]);
        $this->assert($result, ['dog', 'empty_product']);

        $result = $this->execute([['a_text', Operators::DOES_NOT_CONTAIN, 'other']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_text', Operators::EQUALS, 'cats']]);
        $this->assert($result, []);

        $result = $this->execute([['a_text', Operators::EQUALS, 'cat']]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_text', Operators::IS_EMPTY, null]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_text', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_text', Operators::NOT_EQUAL, 'dog']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_text', Operators::NOT_EQUAL, 'cat']]);
        $this->assert($result, ['cattle', 'dog']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_text" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['a_text', Operators::NOT_EQUAL, [[]]]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_text" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['a_text', Operators::GREATER_OR_EQUAL_THAN, 'dog']]);
    }
}
