<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

class ElasticsearchSelectLastConnectionBusinessErrorsQueryIntegration extends TestCase
{
    /** @var Client */
    private $esClient;

    /** @var SelectLastConnectionBusinessErrorsQuery */
    private $selectLastConnectionBusinessErrorsQuery;

//    public function test_it_returns_an_empty_array_when_nothing_is_indexed(): void
//    {
//        $result = $this->selectLastConnectionBusinessErrorsQuery->execute('erp', '2020-01-07', 100);
//
//        Assert::assertEquals([], $result);
//    }

    public function test_it_returns_the_last_business_errors_of_a_connection(): void
    {
        $this->esClient->bulkIndexes([
            // Ignored: error is too older (more than 7 days)
            [
                'connection_code' => 'erp',
                'date_time' => '2019-12-31 00:00:00',
                'content' => json_decode('{"message": "Error 1"}', true),
            ],
            // Ignored: 3rd result (oldest) on a $limit if 2
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-01 00:00:00',
                'content' => json_decode('{"message": "Error 2"}', true),
            ],
            // Ignored: wrong connection code
            [
                'connection_code' => 'ecommerce',
                'date_time' => '2020-01-05 00:00:00',
                'content' => json_decode('{"message": "Error 3"}', true),
            ],
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-06 00:00:00',
                'content' => json_decode('{"message": "Error 4"}', true),
            ],
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-07 00:00:00',
                'content' => json_decode('{"message": "Error 5"}', true),
            ],
            // Ignored: error is younger than the $endDate param
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-08 00:00:00',
                'content' => json_decode('{"message": "Error 6"}', true),
            ],
        ]);
        $this->esClient->refreshIndex();

        $expectedResult = [
            new BusinessError('erp', new \DateTimeImmutable('2020-01-07T00:00:00+00'), '{"message": "Error 5"}'),
            new BusinessError('erp', new \DateTimeImmutable('2020-01-06T00:00:00+00'), '{"message": "Error 4"}'),
        ];

        $result = $this->selectLastConnectionBusinessErrorsQuery->execute('erp', '2020-01-07', 2);

        Assert::assertEquals($expectedResult, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_connectivity.client.connection_error');
        $this->selectLastConnectionBusinessErrorsQuery = $this->get(
            'akeneo_connectivity_connection.persistence.query.select_last_connection_business_errors'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
