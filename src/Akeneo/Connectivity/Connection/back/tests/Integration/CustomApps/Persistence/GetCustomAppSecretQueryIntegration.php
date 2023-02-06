<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppSecretQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\RandomCodeGenerator;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppSecretQuery
 */
class GetCustomAppSecretQueryIntegration extends TestCase
{
    private ?Connection $connection;
    private ?GetCustomAppSecretQuery $getCustomAppSecretQuery;
    private ?RandomCodeGeneratorInterface $randomCodeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->getCustomAppSecretQuery = $this->get(GetCustomAppSecretQuery::class);
        $this->randomCodeGenerator = $this->get(RandomCodeGenerator::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_the_secret_associated_to_a_custom_app_client_id(): void
    {
        $this->createAdminUser();
        $clientId = Uuid::uuid4()->toString();
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);
        $this->createCustomApp($clientId, $clientSecret);

        $result = $this->getCustomAppSecretQuery->execute($clientId);
        Assert::assertSame($clientSecret, $result);
    }

    public function test_it_returns_null_if_the_secret_is_not_found(): void
    {
        $clientId = Uuid::uuid4()->toString();
        $result = $this->getCustomAppSecretQuery->execute($clientId);
        Assert::assertNull($result);
    }

    private function createCustomApp(string $clientId, string $clientSecret): void
    {
        $sql = <<<SQL
        INSERT INTO akeneo_connectivity_test_app (name, activate_url, callback_url, client_secret, client_id, user_id)
        VALUES ('Any custom app name', 'http://activate-url.test', 'http://callback-url.test', :clientSecret, :clientId, :userId)
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
