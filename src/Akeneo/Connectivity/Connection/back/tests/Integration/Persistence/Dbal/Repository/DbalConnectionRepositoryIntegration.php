<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository\DbalConnectionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalConnectionRepositoryIntegration extends TestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    /** @var DbalConnectionRepository */
    private $repository;

    /** @var ConnectionLoader */
    private $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get('akeneo_connectivity.connection.persistence.repository.connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_finds_one_connection_by_code()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

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
    }

    public function test_it_updates_a_connection_from_its_code()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $connection = $this->repository->findOneByCode('magento');
        $connection->setLabel(new ConnectionLabel('Pimgento'));
        $connection->setFlowType(new FlowType(FlowType::OTHER));
        $connection->setImage(new ConnectionImage('a/b/c/app_image.jpg'));

        $this->repository->update($connection);

        $result = $this->selectConnectionFromDb('magento');

        Assert::assertSame('magento', $result['code']);
        Assert::assertSame('Pimgento', $result['label']);
        Assert::assertSame(FlowType::OTHER, $result['flow_type']);
        Assert::assertSame('a/b/c/app_image.jpg' , $result['image']);
        Assert::assertNotNull($result['client_id']);
        Assert::assertNotNull($result['user_id']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function selectConnectionFromDb(string $code): array
    {
        $query = <<<SQL
    SELECT code, label, flow_type, client_id, user_id, image
    FROM akeneo_connectivity_connection
    WHERE code = :code
SQL;
        $statement = $this->dbalConnection->executeQuery($query, ['code' => $code]);

        return $statement->fetch();
    }
}
