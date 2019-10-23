<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Repository;

use Akeneo\Apps\Domain\Model\Read\App as ReadApp;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;
use Akeneo\Apps\Infrastructure\Persistence\Dbal\Repository\DbalAppRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalAppRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $dbal;

    /** @var DbalAppRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbal = $this->get('database_connection');
        $this->repository = $this->get('akeneo_app.persistence.repository.app');
    }

    public function test_it_saves_a_new_app()
    {
        $clientId = $this->createClient('magento');
        $uuid = $this->repository->generateId();

        $this->repository->create(
            new WriteApp(
                $uuid,
                'magento',
                'Magento connector',
                FlowType::DATA_DESTINATION,
                $clientId
            )
        );

        $query = <<<SQL
    SELECT BIN_TO_UUID(id) AS id, code, label, flow_type, client_id
    FROM akeneo_app
    WHERE code = :code
SQL;
        $statement = $this->dbal->executeQuery($query, ['code' => 'magento']);
        $result = $statement->fetch();

        Assert::assertSame($uuid, $result['id']);
        Assert::assertSame('magento', $result['code']);
        Assert::assertSame('Magento connector', $result['label']);
        Assert::assertSame(FlowType::DATA_DESTINATION, $result['flow_type']);
        Assert::assertSame($clientId->id(), (int) $result['client_id']);
    }

    public function test_it_fetches_all_apps()
    {
        $this->insertApp('magento', 'Magento connector', FlowType::DATA_DESTINATION);
        sleep(1); // Apps are ordered by creation date so you need to have a difference to assert the order
        $this->insertApp('erp', 'ERP Connector', FlowType::DATA_SOURCE);

        $apps = $this->repository->fetchAll();

        Assert::assertCount(2, $apps);
        Assert::assertInstanceOf(ReadApp::class, $apps[0]);
        Assert::assertSame('magento', $apps[0]->code());
        Assert::assertSame('Magento connector', $apps[0]->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, $apps[0]->flowType());

        Assert::assertInstanceOf(ReadApp::class, $apps[1]);
        Assert::assertSame('erp', $apps[1]->code());
        Assert::assertSame('ERP Connector', $apps[1]->label());
        Assert::assertSame(FlowType::DATA_SOURCE, $apps[1]->flowType());
    }

    private function insertApp(string $code, string $label, string $flowType): void
    {
        $clientId = $this->createClient($label);
        $id = $this->repository->generateId();

        $insertSql = <<<SQL
    INSERT INTO akeneo_app (id, client_id, code, label, flow_type)
    VALUES (UUID_TO_BIN(:id), :client_id, :code, :label, :flow_type)
SQL;

        $this->dbal->executeQuery(
            $insertSql,
            ['id' => $id, 'client_id' => $clientId->id(), 'code' => $code, 'label' => $label, 'flow_type' => $flowType]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createClient(string $label): ClientId
    {
        return $this
            ->get('akeneo_app.service.client.create_client')
            ->execute($label);
    }
}
