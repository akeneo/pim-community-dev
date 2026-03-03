<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\CreateCustomAppQuery;
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
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\CreateCustomAppQuery
 */
class CreateCustomAppQueryIntegration extends TestCase
{
    private ?Connection $connection;
    private ?CreateCustomAppQuery $createCustomAppQuery;
    private ?RandomCodeGeneratorInterface $randomCodeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->createCustomAppQuery = $this->get(CreateCustomAppQuery::class);
        $this->randomCodeGenerator = $this->get(RandomCodeGenerator::class);
    }
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_a_custom_app(): void
    {
        $this->createAdminUser();
        $clientId = Uuid::uuid4()->toString();
        $clientSecret = \substr($this->randomCodeGenerator->generate(), 0, 100);
        $this->createCustomAppQuery->execute(
            $clientId,
            'Any new name',
            'http://activate-url.test',
            'http://callback-url.test',
            $clientSecret,
            (int) $this->getAdminId(),
        );
        $this->assertCustomAppIsCreated($clientId);
    }

    private function getAdminId()
    {
        return $this->connection->fetchOne('SELECT id from oro_user LIMIT 1');
    }

    private function assertCustomAppIsCreated(string $clientId): void
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
