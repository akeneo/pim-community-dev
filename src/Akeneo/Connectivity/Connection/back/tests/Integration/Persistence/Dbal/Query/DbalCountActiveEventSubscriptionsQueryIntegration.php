<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class DbalCountActiveEventSubscriptionsQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private WebhookLoader $webhookLoader;
    private CountActiveEventSubscriptionsQuery $countActiveEventSubscriptionQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->countActiveEventSubscriptionQuery = $this->get('akeneo_connectivity.connection.persistence.query.count_active_event_subscriptions');
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
