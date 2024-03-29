<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateEventSubscriptionEndToEnd extends WebTestCase
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

    public function test_it_updates_an_event_subscription(): void
    {
        $connection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_SOURCE,
            false,
        );

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'is_using_uuid' => true,
            'url' => 'http://example.test',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data, JSON_THROW_ON_ERROR),
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_fails_to_enable_an_event_subscription_without_url(): void
    {
        $connection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_SOURCE,
            false,
        );

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => null,
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data, JSON_THROW_ON_ERROR),
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedResult = [
            'errors' => [
                [
                    'field' => 'url',
                    'message' => 'akeneo_connectivity.connection.webhook.error.required',
                ],
            ],
            'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
        ];

        Assert::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode(),
        );
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_webhook_to_enabled_with_empty_url(): void
    {
        $connection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_SOURCE,
            false,
        );

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => '',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data, JSON_THROW_ON_ERROR),
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedResult = [
            'errors' => [
                [
                    'field' => 'url',
                    'message' => 'akeneo_connectivity.connection.webhook.error.required',
                ],
            ],
            'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
        ];

        Assert::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode(),
        );
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_webhook_from_an_unknown_connection(): void
    {
        $data = [
            'code' => 'shopify',
            'enabled' => true,
            'url' => 'http://example.test',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections/shopify/webhook',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data),
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedResult = [
            'errors' => [
                [
                    'field' => 'code',
                    'message' => 'akeneo_connectivity.connection.webhook.error.not_found',
                ],
            ],
            'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
        ];

        Assert::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode(),
        );
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_enable_an_event_subscription_once_the_limit_of_active_subscription_is_reached(): void
    {
        // Enable enough event subscription to reach the limit.
        $erpConnection = $this->connectionLoader->createConnection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            false,
        );
        $this->webhookLoader->initWebhook($erpConnection->code());
        $damConnection = $this->connectionLoader->createConnection(
            'dam',
            'DAM',
            FlowType::DATA_SOURCE,
            false,
        );
        $this->webhookLoader->initWebhook($damConnection->code());
        $ecommerceConnection = $this->connectionLoader->createConnection(
            'ecommerce',
            'E-Commerce',
            FlowType::DATA_DESTINATION,
            false,
        );
        $this->webhookLoader->initWebhook($ecommerceConnection->code());

        // New event subscription to enable.
        $translationConnection = $this->connectionLoader->createConnection(
            'translation',
            'Translation',
            FlowType::DATA_SOURCE,
            false,
        );

        $data = [
            'code' => $translationConnection->code(),
            'enabled' => true,
            'url' => 'http://example.test',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook', $translationConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data, JSON_THROW_ON_ERROR),
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode(),
        );
        Assert::assertEquals(
            [
                'errors' => [
                    [
                        'field' => 'enabled',
                        'message' => 'akeneo_connectivity.connection.webhook.error.limit_reached',
                    ],
                ],
                'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
            ],
            $result,
        );
    }

    public function test_it_fails_to_update_a_webhook_with_a_forbidden_url(): void
    {
        $connection = $this->connectionLoader->createConnection(
            'magento',
            'Magento',
            FlowType::DATA_SOURCE,
            false,
        );

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => 'http://localhost',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode($data, JSON_THROW_ON_ERROR),
        );

        Assert::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode(),
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
