<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\GetTestAppSecretQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\RandomCodeGenerator;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class GetTestAppSecretQueryIntegration extends TestCase
{
    private Connection $connection;
    private GetTestAppSecretQuery $getTestAppSecretQuery;
    private RandomCodeGeneratorInterface $randomCodeGenerator;

    public function test_it_gets_the_secret_associated_to_a_test_app_client_id(): void
    {
        $this->createAdminUser();
        $clientId = Uuid::uuid4()->toString();
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);
        $this->createTestApp($clientId, $clientSecret);

        $result = $this->getTestAppSecretQuery->execute($clientId);
        Assert::assertSame($clientSecret, $result);
    }

    public function test_it_returns_null_if_the_secret_is_not_found(): void
    {
        $clientId = Uuid::uuid4()->toString();
        $result = $this->getTestAppSecretQuery->execute($clientId);
        Assert::assertNull($result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->getTestAppSecretQuery = $this->get(GetTestAppSecretQuery::class);
        $this->randomCodeGenerator = $this->get(RandomCodeGenerator::class);
    }

    private function createTestApp(string $clientId, string $clientSecret): void
    {
        $sql = <<<SQL
INSERT INTO akeneo_connectivity_test_app (name, activate_url, callback_url, client_secret, client_id, user_id)
VALUES ('Any test app name', 'http://activate-url.test', 'http://callback-url.test', :clientSecret, :clientId, :userId)
SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'userId' => (int) $this->getAdminId(),
            ]
        );
    }

    private function getAdminId()
    {
        return $this->connection->fetchOne('SELECT id from oro_user LIMIT 1');
    }
}
