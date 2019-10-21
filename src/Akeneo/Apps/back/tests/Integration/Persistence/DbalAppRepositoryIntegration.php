<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence;

use Akeneo\Apps\Domain\Model\ClientId;
use Akeneo\Apps\Domain\Model\Read\App as ReadApp;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;
use Akeneo\Apps\Domain\Model\Write\AppCode;
use Akeneo\Apps\Domain\Model\Write\AppLabel;
use Akeneo\Apps\Domain\Model\Write\FlowType;
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
    private $appRepository;

    public function test_it_saves_a_new_app()
    {
        $id = $this->getClientId('magento');
        $this->appRepository->create(WriteApp::create(
            AppCode::create('magento'),
            AppLabel::create('Magento connector'),
            FlowType::create(FlowType::DATA_DESTINATION),
            ClientId::create($id)
        ));

        $query = <<<SQL
    SELECT *
    FROM akeneo_app
    WHERE code = :code
SQL;
        $statement = $this->dbal->executeQuery($query, ['code' => 'magento']);
        $result = $statement->fetch();

        Assert::assertSame('Magento connector', $result['label']);
        Assert::assertSame(FlowType::DATA_DESTINATION, $result['flow_type']);
        Assert::assertSame($id, (int) $result['client_id']);
    }

    public function test_it_fetches_all_apps()
    {
        $this->insertApps();
        $apps = $this->appRepository->fetchAll();

        Assert::assertCount(2, $apps);
        Assert::assertInstanceOf(ReadApp::class, $apps[0]);
        Assert::assertSame('magento', $apps[0]->code());
        Assert::assertSame('Magento connector', $apps[0]->label());
        Assert::assertSame('data_destination', $apps[0]->flowType());

        Assert::assertInstanceOf(ReadApp::class, $apps[1]);
        Assert::assertSame('erp', $apps[1]->code());
        Assert::assertSame('ERP Connector', $apps[1]->label());
        Assert::assertSame('data_source', $apps[1]->flowType());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->appRepository = $this->get('akeneo_app.persistence.repository.app');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertApps(): void
    {
        $magentoClientId = $this->getClientId('magento');
        $erpClientId = $this->getClientId('erp');
        $insertClient = <<<SQL
    INSERT INTO akeneo_app (client_id, code, label, flow_type)
    VALUES
        (:magento_id, 'magento', 'Magento connector', 'data_destination'),
        (:erp_id, 'erp', 'ERP Connector', 'data_source')
SQL;

        $this->dbal->executeQuery($insertClient, ['magento_id' => $magentoClientId, 'erp_id' => $erpClientId]);
    }

    private function getClientId(string $label): int
    {
        $insertClient = <<<SQL
    INSERT INTO pim_api_client (random_id, redirect_uris, secret, allowed_grant_types, label)
    VALUES ('1234', '', 'secret', 'blop', :label)
SQL;
        $selectClient = <<<SQL
    SELECT id
    FROM pim_api_client
    WHERE label = :label
SQL;

        $this->dbal->executeQuery($insertClient, ['label' => $label]);

        $selectStmt = $this->dbal->executeQuery($selectClient, ['label' => $label]);
        $result = $selectStmt->fetch();

        return (int) $result['id'];
    }
}
