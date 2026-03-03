<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
        $sapConnection = $this->getConnection();
        $stack = $this->getHandlerStack();
        $this->authenticateAsAdmin();

        $stack->setHandler(
            new MockHandler(
                [
                    new Response(200, [], null, '1.1', 'OK'),
                ]
            )
        );

        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook/check-reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode(['url' => 'http://www.get-response-200.com'])
        );

        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($result);
        Assert::assertEquals(['success' => true, 'message' => '200 OK'], $result);
    }

    public function test_it_does_not_reach_webhook_because_of_wrong_url(): void
    {
        $sapConnection = $this->getConnection();
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook/check-reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode(['url' => 'I_AM_NOT_AN_URL'])
        );

        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($result);
        Assert::assertEquals(['success' => false, 'message' => 'This value is not a valid URL.'], $result);
    }

    public function test_it_does_not_reach_webhook_because_of_empty_url(): void
    {
        $sapConnection = $this->getConnection();
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook/check-reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode(['url' => ''])
        );

        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($result);
        Assert::assertEquals(['success' => false, 'message' => 'This value should not be blank.'], $result);
    }

    public function test_it_does_reach_webhook_but_returns_http_error(): void
    {
        $sapConnection = $this->getConnection();
        $stack = $this->getHandlerStack();
        $this->authenticateAsAdmin();

        $stack->setHandler(
            new MockHandler(
                [
                    new RequestException(
                        'RequestException Message',
                        new Request('POST', 'http://www.get-response-451.com'),
                        new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons')
                    ),
                ]
            )
        );

        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook/check-reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode(['url' => 'http://www.get-response-451.com'])
        );

        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($result);
        Assert::assertEquals(['success' => false, 'message' => '451 Unavailable For Legal Reasons'], $result);
    }

    public function test_it_does_not_reach_webhook(): void
    {
        $sapConnection = $this->getConnection();
        $stack = $this->getHandlerStack();
        $this->authenticateAsAdmin();

        $stack->setHandler(
            new MockHandler(
                [
                    new RequestException(
                        'Failed to connect to server',
                        new Request('POST', 'http://www.get-response-451.com'),
                    ),
                ]
            )
        );

        $this->client->request(
            'POST',
            \sprintf('/rest/connections/%s/webhook/check-reachability', $sapConnection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            \json_encode(['url' => 'http://www.get-response-451.com'])
        );

        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($result);
        Assert::assertEquals(['success' => false, 'message' => 'Failed to connect to server'], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getHandlerStack(): HandlerStack
    {
        return $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
    }

    private function getConnection(): ConnectionWithCredentials
    {
        return $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
    }
}
