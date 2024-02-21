<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence\DbalConnectionRepository;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

class DbalConnectionRepositoryIntegration extends TestCase
{
    private DbalConnection $dbalConnection;
    private DbalConnectionRepository $repository;
    private ConnectionLoader $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get(DbalConnectionRepository::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_finds_one_connection_by_code(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);

        $connection = $this->repository->findOneByCode('magento');

        Assert::assertInstanceOf(Connection::class, $connection);
        Assert::assertSame('magento', (string) $connection->code());
        Assert::assertSame('Magento Connector', (string) $connection->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, (string) $connection->flowType());
        Assert::assertNull($connection->image());
        Assert::assertIsInt($connection->clientId()->id());
        Assert::assertGreaterThan(0, $connection->clientId()->id());
        Assert::assertIsInt($connection->userId()->id());
        Assert::assertGreaterThan(0, $connection->userId()->id());
        Assert::assertIsBool($connection->auditable());
        Assert::assertSame('default', (string) $connection->type());
    }

    public function test_it_updates_a_connection_from_its_code(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);

        $connection = $this->repository->findOneByCode('magento');
        $connection->setLabel(new ConnectionLabel('Pimgento'));
        $connection->setFlowType(new FlowType(FlowType::OTHER));
        $connection->setImage(new ConnectionImage('a/b/c/app_image.jpg'));

        $this->repository->update($connection);

        $result = $this->selectConnectionFromDb('magento');

        Assert::assertSame('magento', $result['code']);
        Assert::assertSame('Pimgento', $result['label']);
        Assert::assertSame(FlowType::OTHER, $result['flow_type']);
        Assert::assertSame('a/b/c/app_image.jpg', $result['image']);
        Assert::assertNotNull($result['client_id']);
        Assert::assertNotNull($result['user_id']);
    }

    public function test_it_creates_a_connection(): void
    {
        $user = $this->createAdminUser();

        $clientId = $this->createClient('new_client');

        $connection = new Connection(
            'new_connection',
            'new_connection_label',
            FlowType::OTHER,
            $clientId,
            $user->getId(),
            null,
            true,
            'connection_type'
        );

        $this->repository->create($connection);

        $result = $this->selectConnectionFromDb('new_connection');

        Assert::assertSame('new_connection', $result['code']);
        Assert::assertSame('new_connection_label', $result['label']);
        Assert::assertSame(FlowType::OTHER, $result['flow_type']);
        Assert::assertNotNull($result['client_id']);
        Assert::assertNotNull($result['user_id']);
        Assert::assertTrue((bool) $result['auditable']);
        Assert::assertSame('connection_type', $result['type']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function selectConnectionFromDb(string $code): array
    {
        $query = <<<SQL
    SELECT code, label, flow_type, client_id, user_id, image, auditable, type
    FROM akeneo_connectivity_connection
    WHERE code = :code
SQL;
        $statement = $this->dbalConnection->executeQuery($query, ['code' => $code]);

        return $statement->fetchAssociative();
    }

    private function createClient(string $label): int
    {
        $this->dbalConnection->insert(
            'pim_api_client',
            [
                'label' => $label,
                'random_id' => $label,
                'secret' => $label,
                'allowed_grant_types' => [],
                'redirect_uris' => [],
            ],
            [
                'allowed_grant_types' => Types::ARRAY,
                'redirect_uris' => Types::ARRAY
            ]
        );

        return (int) $this->dbalConnection->lastInsertId();
    }
}
