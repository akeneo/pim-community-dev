<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\ConnectionWebhookRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalConnectionWebhookRepositoryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var Connection */
    private $dbalConnection;

    /** @var ConnectionWebhookRepository */
    private $repository;

    public function test_it_updates_a_webhook(): void
    {
        $magento = $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $this->assertWebhookIsInDatabase($magento->code(), false);
        $numberOfUpdatedRows = $this->repository->update(new ConnectionWebhook($magento->code(), true, 'http://any-url.com'));
        Assert::assertEquals(1, $numberOfUpdatedRows);
        $this->assertWebhookIsInDatabase($magento->code(), true, 'http://any-url.com');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get('akeneo_connectivity.connection.persistence.repository.webhook');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertWebhookIsInDatabase(string $code, bool $enabled, ?string $url = null): void
    {
        $selectQuery = <<<SQL
SELECT webhook_enabled, webhook_url
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;
        $webhook = $this->dbalConnection->executeQuery($selectQuery, ['code' => $code])->fetch();

        Assert::assertEquals($enabled, (bool) $webhook['webhook_enabled']);
        Assert::assertEquals($url, $webhook['webhook_url']);
    }
}
