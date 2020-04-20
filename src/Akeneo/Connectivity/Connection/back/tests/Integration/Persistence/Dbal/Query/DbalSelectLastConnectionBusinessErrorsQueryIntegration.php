<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalSelectLastConnectionBusinessErrorsQueryIntegration extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var SelectLastConnectionBusinessErrorsQuery */
    private $selectLastConnectionBusinessErrorsQuery;

    public function test_it_selects_the_last_business_errors_of_a_connection(): void
    {
        $this->insertBusinessErrors([
            ['erp', '2019-12-31 00:00:00', '{"message": "Error 1"}'], // Ignored: error is older than the param $after
            ['erp', '2020-01-01 00:00:00', '{"message": "Error 2"}'], // Ignored: 3rd result (oldest) on a $limit if 2
            ['ecommerce', '2020-01-05 00:00:00', '{"message": "Error 3"}'], // Ignored: wrong connection $code
            ['erp', '2020-01-06 00:00:00', '{"message": "Error 4"}'],
            ['erp', '2020-01-07 00:00:00', '{"message": "Error 5"}'],
        ]);

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

        $this->connection = $this->get('database_connection');
        $this->selectLastConnectionBusinessErrorsQuery = $this->get(
            'akeneo_connectivity_connection.persistence.query.select_last_connection_business_errors'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertBusinessErrors(array $errors): void
    {
        foreach ($errors as [$connectionCode, $dateTime, $content]) {
            $this->connection->insert('akeneo_connectivity_connection_audit_business_error', [
                'connection_code' => $connectionCode,
                'error_datetime' => $dateTime,
                'content' => $content
            ]);
        }
    }
}
