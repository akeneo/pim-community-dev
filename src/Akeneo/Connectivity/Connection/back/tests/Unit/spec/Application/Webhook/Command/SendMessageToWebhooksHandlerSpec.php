<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendMessageToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectConnectionsWebhookQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GuzzleWebhookClient;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;

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
        $client,
        $builder
    ): void {

        $webhookBynder = new ConnectionWebhook('bynder','7', '5', 'secret_bynder','http://172.17.0.1:8000/webhook_bynder');
        $webhookMagento = new ConnectionWebhook('magento','11', '5', 'secret_magento','http://172.17.0.1:8000/webhook_magento');
        $businessEvent = new BusinessEvent();

        $connectionWebhooks = [
            $webhookBynder,
            $webhookMagento
        ];

        $webhookEvent = new WebhookEvent(
            $businessEvent->name(),
            $businessEvent->uuid(),
            date(\DateTimeInterface::ATOM, $businessEvent->timestamp()),
            $businessEvent->data()
        );

        $command = new SendMessageToWebhooksCommand($businessEvent);

        $selectConnectionsWebhookQuery->execute()->willReturn($connectionWebhooks);

        $builder->build($webhookMagento, $businessEvent)->willReturn($webhookEvent);
        $builder->build($webhookBynder, $businessEvent)->willReturn($webhookEvent);

        $client->bulkSend(Argument::that(function (array $params) {
            Assert::assertCount(2, $params);
            Assert::assertInstanceOf(WebhookRequest::class, $params[0]);
            Assert::assertInstanceOf(WebhookRequest::class, $params[1]);
            Assert::assertEquals($params[0]->webhook()->connectionCode(), 'bynder');
            Assert::assertEquals($params[1]->webhook()->connectionCode(), 'magento');
            return true;
        }))->shouldBeCalled();

        $this->handle($command);
    }
}

class BusinessEvent implements BusinessEventInterface
{
    public function name(): string
    {
        return 'product.updated';
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
