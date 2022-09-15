<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\DbalSelectActiveWebhooksQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalSelectActiveWebhooksQueryIntegration extends TestCase
{
    private ?ConnectionLoader $connectionLoader;
    private ?SelectActiveWebhooksQueryInterface $selectActiveWebhooksQuery;
    private ?DbalConnection $dbalConnection;
    private ?UpdateConnectionHandler $updateConnectionHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectActiveWebhooksQuery = $this->get(DbalSelectActiveWebhooksQuery::class);
        $this->dbalConnection = $this->get('database_connection');
        $this->updateConnectionHandler = $this->get(UpdateConnectionHandler::class);
    }

    public function test_it_finds_connections_webhook(): void
    {
        $magento = $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $erp = $this->connectionLoader->createConnection('erp', 'ERP Connector', FlowType::DATA_SOURCE, false);
        $binder = $this->connectionLoader->createConnection('binder', 'Binder Connector', FlowType::DATA_SOURCE, false);
        $sap = $this->connectionLoader->createConnection('sap', 'Sap Connector', FlowType::DATA_DESTINATION, false);

        // Add more than one user group
        $this->updateConnectionHandler->handle(
            new UpdateConnectionCommand(
                $binder->code(),
                $binder->label(),
                $binder->flowType(),
                $binder->image(),
                $binder->userRoleId(),
                $this->getUserGroupId('IT support'),
                $binder->auditable()
            )
        );

        $this->updateConnection($magento, 'secret_magento', null, true, false);
        $this->updateConnection($binder, 'secret_binder', 'http://localhost/webhook_binder', true, false);
        $this->updateConnection($erp, 'secret_erp', 'http://localhost/webhook_erp', true, true);
        $this->updateConnection($sap, 'secret_sap', 'http://localhost/webhook_sap', false, false);

        $connections = $this->selectActiveWebhooksQuery->execute();

        $binder = $connections[0];
        $erp = $connections[1];

        Assert::assertCount(2, $connections);
        Assert::assertEquals('binder', $binder->connectionCode());
        Assert::assertEquals('secret_binder', $binder->secret());
        Assert::assertEquals('http://localhost/webhook_binder', $binder->url());
        Assert::assertFalse($binder->isUsingUuid());
        Assert::assertEquals('erp', $erp->connectionCode());
        Assert::assertEquals('secret_erp', $erp->secret());
        Assert::assertEquals('http://localhost/webhook_erp', $erp->url());
        Assert::assertTrue($erp->isUsingUuid());
    }

    public function test_it_does_not_find_anything_if_no_connection_webhook_is_configured(): void
    {
        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, false);
        $ecommerce = $this->connectionLoader->createConnection('ecommerce', 'eCommerce', FlowType::DATA_DESTINATION, false);

        $this->updateConnection($ecommerce, 'secret', 'http://localhost/webhook', false, true);

        $connections = $this->selectActiveWebhooksQuery->execute();

        Assert::assertCount(0, $connections);
    }

    private function getUserGroupId(string $name): string
    {
        $sql = <<<SQL
    SELECT * FROM oro_access_group WHERE name = :name
SQL;

        return $this->dbalConnection->executeQuery(
            $sql,
            [
                'name' => $name,
            ]
        )->fetchOne();
    }

    private function updateConnection(
        ConnectionWithCredentials $connection,
        string $secret,
        ?string $url,
        bool $enabled,
        bool $isUsingUuid,
    ): void {
        $this->dbalConnection->update(
            'akeneo_connectivity_connection',
            [
                'webhook_url' => $url,
                'webhook_secret' => $secret,
                'webhook_enabled' => (int) $enabled,
                'webhook_is_using_uuid' => (int) $isUsingUuid,
            ],
            ['code' => $connection->code()]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
