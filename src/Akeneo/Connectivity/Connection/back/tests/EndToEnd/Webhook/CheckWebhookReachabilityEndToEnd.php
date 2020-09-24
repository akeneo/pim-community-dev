<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityEndToEnd extends WebTestCase
{
    public function test_it_does_reach_webhook(): void
    {
        $expectedResult = [
            'success' => true,
            'message' => '200 OK',
        ];

        $data = [
            'url' => 'http://www.get-response-200.com',
        ];

        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook/check_reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertIsArray($result);
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_does_not_reach_webhook_because_of_wrong_url(): void
    {
        $expectedResult = [
            'success' => false,
            'message' => 'akeneo_connectivity.connection.webhook.error.wrong_url',
        ];

        $data = [
            'url' => 'I_AM_NOT_AN_URL',
        ];

        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook/check_reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertIsArray($result);
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_does_reach_webhook_but_returns_http_error(): void
    {
        $expectedResult = [
            'success' => false,
            'message' => '451 Unavailable For Legal Reasons',
        ];

        $data = [
            'url' => 'http://www.get-response-451.com',
        ];

        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook/check_reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertIsArray($result);
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_does_not_reach_webhook(): void
    {
        $expectedResult = [
            'success' => false,
            'message' => 'Failed to connect to server',
        ];

        $data = [
            'url' => 'http://www.unreachable-url.com',
        ];

        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            sprintf('/rest/connections/%s/webhook/check_reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertIsArray($result);
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
