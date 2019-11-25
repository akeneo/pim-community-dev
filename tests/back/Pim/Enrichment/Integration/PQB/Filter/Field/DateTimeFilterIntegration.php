<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @var array
     */
    private $createdDates = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createdDates['before_first'] = new \DateTime('2018-05-23 00:00:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('foo',new \DateTime('2018-05-23 00:01:00', new \DateTimeZone('UTC')));
        $this->createdDates['before_second'] = new \DateTime('2018-05-23 00:02:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('bar', new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC')));
        $this->createdDates['before_third'] = new \DateTime('2018-05-23 00:04:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('baz', new \DateTime('2018-05-23 00:05:00', new \DateTimeZone('UTC')));
        $this->createdDates['after_all'] = new \DateTime('2018-05-23 00:06:00', new \DateTimeZone('UTC'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_first']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_second']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_third']]]);
        $this->assert($result, ['foo', 'bar']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['after_all']]]);
        $this->assert($result, ['foo', 'bar', 'baz']);
    }

    public function testOperatorEquals()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $result = $this->executeFilter([['updated', Operators::EQUALS, $now->format('Y-m-d H:i:s')]]);
        $this->assert($result, []);

        $barProductUpdatedDate = new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::EQUALS, $barProductUpdatedDate]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, $this->createdDates['before_second']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, $this->createdDates['before_first']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_EMPTY, null]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorDifferent()
    {
        $barProduct = $this->get('pim_api.repository.product')->findOneByIdentifier('bar');
        $updatedAt = $barProduct->getUpdated();
        $updatedAt->setTimezone(new \DateTimeZone('UTC'));

        $barProductUpdatedDate = new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $barProductUpdatedDate]]);
        $this->assert($result, ['foo', 'baz']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $currentDate->format('Y-m-d H:i:s')]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['before_second'], $this->createdDates['after_all']]]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['before_second'], $this->createdDates['before_third']]]]);
        $this->assert($result, ['bar']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['after_all'], $currentDate]]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['before_second'], $this->createdDates['after_all']]]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['before_second'], $this->createdDates['before_third']]]]);
        $this->assert($result, ['baz', 'foo']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['after_all'], $currentDate]]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "updated" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd H:i:s".');

        $this->executeFilter([['updated', Operators::BETWEEN, []]]);
    }

    public function testErrorDataIsMalformedWithISODate()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "updated" expects a string with the format "yyyy-mm-dd H:i:s" as data, "2016-12-12T00:00:00" given.');

        $this->executeFilter([['updated', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "updated" is not supported or does not support operator "IN CHILDREN"');

        $this->executeFilter([['updated', Operators::IN_CHILDREN_LIST, ['2016-08-29 00:00:01']]]);
    }

    /**
     * We force the updated date in order to avoid to use `sleep` between the creation of products, which is time consuming.
     */
    private function createProductWithFollowingUpdatedDate(string $identifier, \DateTimeInterface $updatedDate): void
    {
        $this->createProduct($identifier, []);

        $sql = <<<SQL
UPDATE pim_catalog_product
SET updated = :updated_date
WHERE identifier = :identifier
SQL;

        $this->get('database_connection')->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'updated_date' => $updatedDate,
            ],
            [
                'updated_date' => Type::DATETIME
            ]);

        $this->get('akeneo.pim.enrichment.elasticsearch.indexer.product_and_ancestors')->indexFromProductIdentifiers([$identifier]);

    }
}
