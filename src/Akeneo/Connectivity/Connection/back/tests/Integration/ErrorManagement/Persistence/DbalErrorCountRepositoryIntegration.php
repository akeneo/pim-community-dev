<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\DbalErrorCountRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalErrorCountRepositoryIntegration extends TestCase
{
    private DbalConnection $dbalConnection;
    private DbalErrorCountRepository $repository;

    public function test_it_inserts_an_error_count(): void
    {
        $this->repositoryUpsert('erp', '2020-05-15 17:27:00', 1618, ErrorTypes::BUSINESS);

        $selectQuery = <<<SQL
SELECT connection_code, error_datetime, error_type, error_count
FROM akeneo_connectivity_connection_audit_error
SQL;
        $result = $this->dbalConnection->executeQuery($selectQuery)->fetchAllAssociative();

        Assert::assertCount(1, $result);
        Assert::assertEquals('2020-05-15 17:00:00', $result[0]['error_datetime']);
        Assert::assertEquals('erp', $result[0]['connection_code']);
        Assert::assertEquals(ErrorTypes::BUSINESS, $result[0]['error_type']);
        Assert::assertEquals('1618', $result[0]['error_count']);
    }

    public function test_it_upserts_an_error_count_within_the_same_hour(): void
    {
        $this->repositoryUpsert('erp', '2020-05-15 17:27:00', 1618, ErrorTypes::BUSINESS);
        $this->repositoryUpsert('erp', '2020-05-15 17:42:00', 42, ErrorTypes::BUSINESS);

        $selectQuery = <<<SQL
SELECT connection_code, error_datetime, error_type, error_count
FROM akeneo_connectivity_connection_audit_error
SQL;
        $result = $this->dbalConnection->executeQuery($selectQuery)->fetchAllAssociative();

        Assert::assertCount(1, $result);
        Assert::assertEquals('2020-05-15 17:00:00', $result[0]['error_datetime']);
        Assert::assertEquals('erp', $result[0]['connection_code']);
        Assert::assertEquals(ErrorTypes::BUSINESS, $result[0]['error_type']);
        Assert::assertEquals('1660', $result[0]['error_count']);
    }

    public function test_it_does_not_upsert_if_the_error_is_not_the_same(): void
    {
        $this->repositoryUpsert('erp', '2020-05-15 17:27:00', 1618, ErrorTypes::BUSINESS);
        // Not the same code
        $this->repositoryUpsert('magento', '2020-05-15 17:42:00', 1618, ErrorTypes::BUSINESS);
        // Not the same type
        $this->repositoryUpsert('erp', '2020-05-15 17:42:00', 1618, ErrorTypes::TECHNICAL);
        // Not the same date
        $this->repositoryUpsert('erp', '2019-12-24 03:15:00', 1618, ErrorTypes::BUSINESS);

        $selectQuery = <<<SQL
SELECT connection_code, error_datetime, error_type, error_count
FROM akeneo_connectivity_connection_audit_error
SQL;
        $result = $this->dbalConnection->executeQuery($selectQuery)->fetchAllAssociative();

        Assert::assertCount(4, $result);
    }

    private function repositoryUpsert(string $connectionCode, string $datetime, int $count, string $type): void
    {
        $interval = HourlyInterval::createFromDateTime(
            new \DateTime($datetime, new \DateTimeZone('UTC'))
        );
        $hourlyErrorCount = new HourlyErrorCount($connectionCode, $interval, $count, $type);

        $this->repository->upsert($hourlyErrorCount);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get(DbalErrorCountRepository::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
