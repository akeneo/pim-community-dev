<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectConnectionsWebhookQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @package   Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsWebhookIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var SelectConnectionsWebhookQuery */
    private $selectConnectionsWebhookQuery;

    /** @var DbalConnection */
    private $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectConnectionsWebhookQuery = $this->get('akeneo_connectivity_connection_persistence_query_select_connections_webhook');
        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_finds_a_connection_with_enabled_webhooks()
    {
        $magento = $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $erp = $this->connectionLoader->createConnection('erp', 'ERP Connector', FlowType::DATA_SOURCE, false);
        $binder = $this->connectionLoader->createConnection('binder', 'Binder Connector', FlowType::DATA_SOURCE, false);
        $sap = $this->connectionLoader->createConnection('sap', 'Sap Connector', FlowType::DATA_DESTINATION, false);

        $this->updateConnection($magento, 'secret_magento', null, 1);
        $this->updateConnection($binder, 'secret_binder', 'http://172.17.0.1:8000/webhook_binder', 1);
        $this->updateConnection($erp, 'secret_erp', 'http://172.17.0.1:8000/webhook_erp', 1);
        $this->updateConnection($sap, 'secret_sap', 'http://172.17.0.1:8000/webhook_sap', 0);

        $connections = $this->selectConnectionsWebhookQuery->execute();

        Assert::assertCount(2, $connections);
        Assert::assertEquals('binder', $connections[0]['code']);
        Assert::assertEquals('secret_binder', $connections[0]['webhook_secret']);
        Assert::assertEquals('http://172.17.0.1:8000/webhook_binder', $connections[0]['webhook_url']);
        Assert::assertEquals('erp', $connections[1]['code']);
        Assert::assertEquals('secret_erp', $connections[1]['webhook_secret']);
        Assert::assertEquals('http://172.17.0.1:8000/webhook_erp', $connections[1]['webhook_url']);
    }

    private function updateConnection(ConnectionWithCredentials $connection, string $secret, ?string $url, int $enabled)
    {
        $this->dbalConnection->update('akeneo_connectivity_connection', [
            'webhook_url' => $url,
            'webhook_secret' => $secret,
            'webhook_enabled' => $enabled], ['code' => $connection->code()]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
