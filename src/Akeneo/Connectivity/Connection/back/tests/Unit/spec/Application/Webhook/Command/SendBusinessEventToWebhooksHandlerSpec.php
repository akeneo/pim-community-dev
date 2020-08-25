<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClient;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQuery;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksHandlerSpec extends ObjectBehavior
{
    public function let(
        SelectActiveWebhooksQuery $selectActiveWebhooksQuery,
        WebhookClient $client,
        WebhookEventBuilder $builder
    ): void {
        $this->beConstructedWith($selectActiveWebhooksQuery, $client, $builder);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SendBusinessEventToWebhooksHandler::class);
    }

    public function it_sends_message_to_webhooks(
        $selectActiveWebhooksQuery,
        $client,
        $builder,
        SendBusinessEventToWebhooksCommand $command,
        BusinessEventInterface $businessEvent,
        ActiveWebhook $webhook1,
        ActiveWebhook $webhook2
    ): void {
        $command->businessEvent()->willReturn($businessEvent);

        $webhook1->userId()->willReturn(0);
        $webhook2->userId()->willReturn(1);
        $webhook2->url()->willReturn('http://localhost/webhook');
        $webhook2->secret()->willReturn('a_secret');

        $selectActiveWebhooksQuery->execute()->willReturn([$webhook1, $webhook2]);

        $builder->build($businessEvent, ['user_id' => 0])->willReturn(new WebhookEvent('', '', '', []));
        $builder->build($businessEvent, ['user_id' => 1])->willReturn(new WebhookEvent(
            'product.created',
            '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
            '2020-01-01T00:00:00+00:00',
            ['code' => 'blue_shoes']
        ));

        $client->bulkSend(Argument::that(function (iterable $iterable) {
            $requests = iterator_to_array($iterable);

            Assert::assertCount(2, $requests);
            Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

            // Test 2nd webhook values:
            Assert::assertEquals('http://localhost/webhook', $requests[1]->url());
            Assert::assertEquals('a_secret', $requests[1]->secret());
            Assert::assertEquals([
                'action' => 'product.created',
                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                'event_date' => '2020-01-01T00:00:00+00:00',
                'data' => ['code' => 'blue_shoes']
            ], $requests[1]->content());

            return true;
        }))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_does_not_send_message_if_there_is_no_webhook(
        $selectActiveWebhooksQuery,
        $client,
        SendBusinessEventToWebhooksCommand $command
    ): void {
        $selectActiveWebhooksQuery->execute()->willReturn([]);

        $client->bulkSend(Argument::any())->shouldNotBeCalled();

        $this->handle($command);
    }
}
