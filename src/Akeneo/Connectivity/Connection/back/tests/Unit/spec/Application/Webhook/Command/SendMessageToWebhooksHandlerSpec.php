<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GuzzleWebhookClient;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendMessageToWebhooksHandlerSpec extends ObjectBehavior
{
    public function let(
        SelectConnectionsWebhookQuery $selectConnectionsWebhookQuery,
        GuzzleWebhookClient $client,
        WebhookEventBuilder $builder
    ): void {
        $this->beConstructedWith($selectConnectionsWebhookQuery, $client, $builder);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendMessageToWebhooksHandler::class);
    }

    public function it_sends_message_to_webhooks(
        $selectConnectionsWebhookQuery,
        $client
    ): void {
        $businessEvent = new BusinessEvent();
        $webhookRequests = [];

        $command = new SendMessageToWebhooksCommand($businessEvent);

        $selectConnectionsWebhookQuery->execute()->shouldBeCalled()->willReturn($webhookRequests);

        $client->bulkSend($webhookRequests)->shouldBeCalled();

        $this->handle($command);
    }
}

class BusinessEvent implements BusinessEventInterface
{
    public function name(): string
    {
        return 'product';
    }

    public function author(): string
    {
        return 'magento_connection';
    }

    public function data(): array
    {
        return [];
    }

    public function timestamp(): int
    {
        return 123456;
    }

    public function uuid(): string
    {
        return 'UUID';
    }

}
