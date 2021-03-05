<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookRequestSpec extends ObjectBehavior
{
    public function let(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $this->beConstructedWith(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook'),
            [
                new WebhookEvent(
                    'product.created',
                    '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['identifier' => '1'],
                    $this->createEvent($author, ['identifier' => '1'])
                ),
            ]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebhookRequest::class);
    }

    public function it_returns_an_url(): void
    {
        $this->url()
            ->shouldReturn('http://localhost/webhook');
    }

    public function it_returns_a_secret(): void
    {
        $this->secret()
            ->shouldReturn('a_secret');
    }

    public function it_returns_a_content(): void
    {
        $this->content()
            ->shouldReturn(
                [
                    'events' => [
                        [
                            'action' => 'product.created',
                            'event_id' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                            'event_datetime' => '2020-01-01T00:00:00+00:00',
                            'author' => 'julia',
                            'author_type' => 'ui',
                            'pim_source' => 'staging.akeneo.com',
                            'data' => ['identifier' => '1'],
                        ],
                    ],
                ]
            );
    }

    public function it_returns_the_webhook(ActiveWebhook $webhook): void
    {
        $this->beConstructedWith(
            $webhook,
            []
        );

        $this->webhook()->shouldReturn($webhook);
    }

    public function it_returns_the_api_events(ActiveWebhook $webhook, WebhookEvent $webhookEvent): void
    {
        $this->beConstructedWith(
            $webhook,
            [$webhookEvent]
        );

        $this->apiEvents()->shouldReturn([$webhookEvent]);
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1577836800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';

        return new class($author, $data, $timestamp, $uuid) extends Event
        {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}
