<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\DbalGetAConnectionWebhookQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\DbalGetAConnectionWebhookQuery
 */
class DbalGetAConnectionWebhookQueryIntegration extends TestCase
{
    private ?ConnectionLoader $connectionLoader;
    private ?GetAConnectionWebhookQueryInterface $getAConnectionWebhookQuery;
    private ?WebhookLoader $webhookLoader;

    public function test_it_gets_an_enabled_connection_webhook_for_a_given_code(): void
    {
        $magento = $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $this->connectionLoader->createConnection('erp', 'ERP Connector', FlowType::DATA_SOURCE, false);
        $this->webhookLoader->initWebhook($magento->code(), true);

        $webhook = $this->getAConnectionWebhookQuery->execute($magento->code());

        Assert::assertInstanceOf(ConnectionWebhook::class, $webhook);
        Assert::assertEquals('magento', $webhook->connectionCode());
        Assert::assertTrue($webhook->enabled());
        Assert::assertTrue($webhook->isUsingUuid());
        Assert::assertEquals('secret', $webhook->secret());
        Assert::assertEquals('http://test.com', $webhook->url());
    }

    public function test_it_gets_a_disabled_connection_webhook_for_a_given_code(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);
        $erp = $this->connectionLoader->createConnection('erp', 'ERP Connector', FlowType::DATA_SOURCE, false);

        $webhook = $this->getAConnectionWebhookQuery->execute($erp->code());

        Assert::assertInstanceOf(ConnectionWebhook::class, $webhook);
        Assert::assertEquals('erp', $webhook->connectionCode());
        Assert::assertFalse($webhook->enabled());
        Assert::assertFalse($webhook->isUsingUuid());
        Assert::assertNull($webhook->secret());
        Assert::assertNull($webhook->url());
    }

    public function test_it_gets_null_if_there_is_no_result(): void
    {
        $webhook = $this->getAConnectionWebhookQuery->execute('amazon');

        Assert::assertNull($webhook);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->getAConnectionWebhookQuery = $this->get(DbalGetAConnectionWebhookQuery::class);
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
