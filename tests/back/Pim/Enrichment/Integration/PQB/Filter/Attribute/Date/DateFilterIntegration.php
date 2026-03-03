<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_date']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetDateValue('a_date', null, null, new \DateTime('2017-02-06'))
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetDateValue('a_date', null, null, new \DateTime('2017-02-27'))
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-06']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-07']]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-28']]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, new \DateTime('2017-02-28T00:00:00')]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_date', Operators::EQUALS, '2017-02-01']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::EQUALS, '2017-02-06']]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_date', Operators::GREATER_THAN, '2017-03-05']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::GREATER_THAN, '2017-02-05']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_date', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_date', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_date', Operators::NOT_EQUAL, '2017-02-20']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testRelativeDates()
    {
        $productsToRemove = $this->get('pim_catalog.repository.product')->getItemsFromIdentifiers(
            ['product_one', 'product_two', 'empty_product']
        );
        $this->get('pim_catalog.remover.product')->removeAll($productsToRemove);

        $currentDate = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->createProduct(
            'product_today',
            [
                new SetFamily('a_family'),
                new SetDateValue('a_date', null, null, new \DateTime($currentDate->format('Y-m-d')))
            ]
        );
        $this->createProduct(
            'product_future',
            [
                new SetFamily('a_family'),
                new SetDateValue('a_date', null, null, new \DateTime($currentDate->modify('+10 days')->format('Y-m-d')))
            ]
        );
        $this->createProduct(
            'product_past',
            [
                new SetFamily('a_family'),
                new SetDateValue('a_date', null, null, new \DateTime($currentDate->modify('-6 weeks')->format('Y-m-d')))
            ]
        );

        $this->assert(
            $this->executeFilter([['a_date', Operators::GREATER_THAN, '+12 days']]),
            []
        );
        $this->assert(
            $this->executeFilter([['a_date', Operators::LOWER_THAN, '+8 days']]),
            ['product_today', 'product_past']
        );
        $this->assert(
            $this->executeFilter([['a_date', Operators::GREATER_THAN, '-5 weeks']]),
            ['product_today', 'product_future']
        );
        $this->assert(
            $this->executeFilter([['a_date', Operators::LOWER_THAN, '-7 weeks']]),
            []
        );
    }

    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_date" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd".');
        $this->executeFilter([['a_date', Operators::BETWEEN, []]]);
    }

    public function testErrorDataIsMalformedWithISODate()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "a_date" expects a string with the format "yyyy-mm-dd" as data, "2016-12-12T00:00:00" given.');
        $this->executeFilter([['a_date', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_date" is not supported or does not support operator "CONTAINS"');
        $this->executeFilter([['a_date', Operators::CONTAINS, '2017-02-07']]);
    }
}
