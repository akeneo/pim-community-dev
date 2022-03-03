<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\CreateTestAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\RandomCodeGenerator;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class CreateTestAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private CreateTestAppQuery $createTestAppQuery;
    private RandomCodeGeneratorInterface $randomCodeGenerator;

    public function test_it_creates_a_test_app(): void
    {
        $this->createAdminUser();
        $clientId = Uuid::uuid4()->toString();
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);
        $this->createTestAppQuery->execute(
            $clientId,
            'Any new name',
            'http://activate-url.test',
            'http://callback-url.test',
            $clientSecret,
            (int) $this->getAdminId(),
        );
        $this->assertTestAppIsCreated($clientId);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->createTestAppQuery = $this->get(CreateTestAppQuery::class);
        $this->randomCodeGenerator = $this->get(RandomCodeGenerator::class);
    }

    private function getAdminId()
    {
        return $this->connection->fetchOne('SELECT id from oro_user LIMIT 1');
    }

    private function assertTestAppIsCreated(string $clientId): void
    {
        $sql = <<<SQL
SELECT 1
FROM akeneo_connectivity_test_app
WHERE client_id = :clientId
SQL;

        $result = $this->connection->fetchOne($sql, ['clientId' => $clientId]);
        Assert::assertNotFalse($result);
    }
}
