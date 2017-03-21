<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Date;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createAttribute([
                'code'                => 'a_localizable_date',
                'type'                => AttributeTypes::DATE,
                'localizable'         => true,
                'scopable'            => false
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_localizable_date' => [
                        ['data' => '2016-04-23', 'locale' => 'en_US', 'scope' => null],
                        ['data' => '2016-05-23', 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_localizable_date' => [
                        ['data' => '2016-09-23', 'locale' => 'en_US', 'scope' => null],
                        ['data' => '2016-09-23', 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_localizable_date', Operators::LOWER_THAN, '2016-04-29', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_date', Operators::LOWER_THAN, '2016-04-29', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_date', Operators::LOWER_THAN, '2016-09-24', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_localizable_date', Operators::EQUALS, '2016-09-23', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_date', Operators::EQUALS, '2016-05-23', ['locale' => 'en_US']]]);
        $this->assert($result, []);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_localizable_date', Operators::GREATER_THAN, '2016-09-23', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_date', Operators::GREATER_THAN, '2016-09-23', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_date', Operators::GREATER_THAN, '2016-09-22', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_localizable_date', Operators::IS_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_localizable_date', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_localizable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorBetween()
    {
        $result = $this->execute([['a_localizable_date', Operators::BETWEEN, ['2016-09-23', '2016-09-23'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_date', Operators::BETWEEN, ['2016-04-23', '2016-09-23'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->execute([['a_localizable_date', Operators::NOT_BETWEEN, ['2016-09-23', '2016-09-23'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_date', Operators::NOT_BETWEEN, [new \DateTime('2016-04-23T00:00:00'), '2016-09-23'], ['locale' => 'en_US']]]);
        $this->assert($result, []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_date" expects a locale, none given.
     */
    public function testErrorMetricLocalizable()
    {
        $this->execute([['a_localizable_date', Operators::NOT_EQUAL, 250]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_date" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_localizable_date', Operators::NOT_EQUAL, 10, ['locale' => 'NOT_FOUND']]]);
    }
}
