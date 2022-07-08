<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Persistence\DbalCountActiveEventSubscriptionsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DbalCountActiveEventSubscriptionsQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private WebhookLoader $webhookLoader;
    private CountActiveEventSubscriptionsQueryInterface $countActiveEventSubscriptionQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->countActiveEventSubscriptionQuery = $this->get(DbalCountActiveEventSubscriptionsQuery::class);
    }

    public function test_it_counts_active_event_subscriptions(): void
    {
        // Create 3 connections.
        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, false);
        $this->connectionLoader->createConnection('ecommerce', 'E-Commerce', FlowType::DATA_DESTINATION, false);
        $this->connectionLoader->createConnection('dam', 'DAM', FlowType::DATA_SOURCE, false);

        // Enable 2 event subscriptions.
        $this->webhookLoader->initWebhook('ecommerce');
        $this->webhookLoader->initWebhook('dam');

        $count = $this->countActiveEventSubscriptionQuery->execute();

        Assert::assertEquals(2, $count);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
