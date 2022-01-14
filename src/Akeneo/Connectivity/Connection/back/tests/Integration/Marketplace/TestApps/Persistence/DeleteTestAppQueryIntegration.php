<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\TestApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\DeleteTestAppQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private DeleteTestAppQuery $deleteTestAppQuery;

    protected function getConfiguration(): ?Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->deleteTestAppQuery = $this->get(DeleteTestAppQuery::class);
    }

    public function test_it_deletes_test_app(): void
    {
        $id = 'test_id';
        $this->addTestApp($id);
        Assert::assertTrue($this->doesTestAppExists($id));

        $this->deleteTestAppQuery->execute($id);

        Assert::assertFalse($this->doesTestAppExists($id));
    }

    public function test_no_error_occurs_when_deleting_non_existent_test_app(): void
    {
        $id = 'test_id';
        Assert::assertFalse($this->doesTestAppExists($id));

        $this->deleteTestAppQuery->execute($id);

        Assert::assertFalse($this->doesTestAppExists($id));
    }

    private function addTestApp(string $id): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', [
            'client_id' => $id,
            'client_secret' => $id,
            'name' => $id,
            'activate_url' => $id,
            'callback_url' => $id,
            'user_id' => null,
        ]);
    }

    private function doesTestAppExists(string $id): bool
    {
        $query = <<<SQL
        SELECT client_id
        FROM akeneo_connectivity_test_app
        WHERE client_id = :client_id
        SQL;

        $result = $this->connection->executeQuery($query, [
            'client_id' => $id,
        ])->fetchOne();

        return false !== $result;
    }
}
