<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateWebhookEndToEnd extends WebTestCase
{
    public function test_it_updates_a_connection_webhook(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => 'http://valid-url.com',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = null;

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_webhook_to_enabled_without_url(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => null,
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            'errors' => [
                [
                    'field' => 'url',
                    'message' => 'akeneo_connectivity.connection.webhook.error.required',
                ],
            ],
            'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception'
        ];

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_webhook_to_enabled_with_empty_url(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);

        $data = [
            'code' => $connection->code(),
            'enabled' => true,
            'url' => '',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            'errors' => [
                [
                    'field' => 'url',
                    'message' => 'akeneo_connectivity.connection.webhook.error.required',
                ],
            ],
            'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
        ];

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_update_a_webhook_from_an_unknown_connection(): void
    {
        $data = [
            'code' => 'shopify',
            'enabled' => true,
            'url' => 'http://valid-url.com',
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections/shopify/webhook',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $expectedResult = 'akeneo_connectivity.connection.webhook.error.not_found';

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
