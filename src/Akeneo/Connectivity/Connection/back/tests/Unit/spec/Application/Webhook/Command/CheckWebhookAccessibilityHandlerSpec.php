<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookAccessibilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookAccessibilityHandler;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookAccessibilityHandlerSpec extends ObjectBehavior
{
    public function let(ClientInterface $client): void
    {
        $this->beConstructedWith($client);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CheckWebhookAccessibilityHandler::class);
    }

    public function it_returns_success($client): void
    {
        $checkWebhookAccessibilityCommand = new CheckWebhookAccessibilityCommand('http://172.17.0.1:8000/webhook');
        $request = new Request($this->getWrappedObject()::POST, $checkWebhookAccessibilityCommand->webhookUrl());

        $client->send($request)->willReturn(new Response(200, [], null, '1.1', 'OK'));

        $this->handle($checkWebhookAccessibilityCommand)->shouldReturn([
            'success' => 'true',
            'message' => 'OK',
            'code' => 200,
        ]);
    }

    public function it_returns_fail_when_webhook_url_format_is_ko($client): void
    {
        $checkWebhookAccessibilityCommand = new CheckWebhookAccessibilityCommand('it_is_not_a_good_url_format');
        $request = new Request($this->getWrappedObject()::POST, $checkWebhookAccessibilityCommand->webhookUrl());

        $client->send($request)->willReturn(new Response(200, [], null, '1.1', 'OK'));

        $this->handle($checkWebhookAccessibilityCommand)->shouldReturn([
            'success' => 'false',
            'message' => 'akeneo_connectivity.connection.webhook.constraint.url.invalid_format',
        ]);
    }

    public function it_returns_fail_when_webhook_is_unavailable($client): void
    {
        $checkWebhookAccessibilityCommand = new CheckWebhookAccessibilityCommand('http://172.17.0.1:8000/webhook');
        $request = new Request($this->getWrappedObject()::POST, $checkWebhookAccessibilityCommand->webhookUrl());
        $response = new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons');
        $requestException = new RequestException('ConnectException message', $request, $response);

        $client->send($request)->willThrow($requestException);

        $this->handle($checkWebhookAccessibilityCommand)->shouldReturn([
            'success' => 'false',
            'message' => 'Unavailable For Legal Reasons',
            'code' => 451,
        ]);
    }

    public function it_returns_fail_when_webhook_url_is_not_reachable($client): void
    {
        $checkWebhookAccessibilityCommand = new CheckWebhookAccessibilityCommand('http://172.17.0.1:8000/webhook');
        $request = new Request($this->getWrappedObject()::POST, $checkWebhookAccessibilityCommand->webhookUrl());
        $connectException = new ConnectException('ConnectException message', $request);

        $client->send($request)->willThrow($connectException);

        $this->handle($checkWebhookAccessibilityCommand)->shouldReturn([
            'success' => 'false',
            'message' => 'Failed to connect to server',
        ]);
    }

    public function it_returns_fail_when_not_a_request_exception_is_raised($client): void
    {
        $checkWebhookAccessibilityCommand = new CheckWebhookAccessibilityCommand('http://172.17.0.1:8000/webhook');
        $request = new Request($this->getWrappedObject()::POST, $checkWebhookAccessibilityCommand->webhookUrl());
        $transferException = new TransferException('ClientException message');

        $client->send($request)->willThrow($transferException);

        $this->handle($checkWebhookAccessibilityCommand)->shouldReturn([
            'success' => 'false',
            'message' => 'Failed to connect to server',
        ]);
    }
}

