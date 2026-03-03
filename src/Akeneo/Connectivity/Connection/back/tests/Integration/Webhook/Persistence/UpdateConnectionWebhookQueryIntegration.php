<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\UpdateConnectionWebhookQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\UpdateConnectionWebhookQuery
 */
class UpdateConnectionWebhookQueryIntegration extends TestCase
{
    private ?ConnectionLoader $connectionLoader;
    private ?Connection $connection;
    private ?UpdateConnectionWebhookQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->query = $this->get(UpdateConnectionWebhookQuery::class);
    }

    public function test_it_updates_a_webhook(): void
    {
        $magento = $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $this->assertWebhookIsInDatabase($magento->code(), false);

        $numberOfUpdatedRows = $this->query->execute(new ConnectionWebhook(
            $magento->code(),
            true,
            'http://any-url.com',
            true
        ));

        Assert::assertEquals(1, $numberOfUpdatedRows);
        $this->assertWebhookIsInDatabase($magento->code(), true, 'http://any-url.com', true);
    }

    private function assertWebhookIsInDatabase(
        string $code,
        bool $enabled,
        ?string $url = null,
        bool $isUsingUuid = false,
    ): void {
        $selectQuery = <<<SQL
        SELECT webhook_enabled, webhook_url, webhook_is_using_uuid
        FROM akeneo_connectivity_connection
        WHERE code = :code
        SQL;
        $webhook = $this->connection->executeQuery($selectQuery, ['code' => $code])->fetchAssociative();

        Assert::assertEquals($enabled, (bool) $webhook['webhook_enabled']);
        Assert::assertEquals($url, $webhook['webhook_url']);
        Assert::assertEquals($isUsingUuid, (bool) $webhook['webhook_is_using_uuid']);
    }
}
