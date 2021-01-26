<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEventSubscriptionFormDataEndToEnd extends WebTestCase
{
    private ConnectionLoader $connectionLoader;
    private WebhookLoader $webhookLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.connection_loader',
        );
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
    }

    public function test_it_gets_an_event_subscription_form_data(): void
    {
        $this->connectionLoader->createConnection('dam', 'DAM', FlowType::DATA_SOURCE, false);

        $erpConnection = $this->connectionLoader->createConnection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            false,
        );
        $this->webhookLoader->initWebhook($erpConnection->code());

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            sprintf('/rest/connections/%s/webhook', $erpConnection->code()),
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(
            [
                'event_subscription' => [
                    'connectionCode' => 'erp',
                    'enabled' => true,
                    'secret' => 'secret',
                    'url' => 'http://test.com',
                ],
                'active_event_subscriptions_limit' => [
                    'limit' => 3,
                    'current' => 1,
                ],
            ],
            $result,
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
