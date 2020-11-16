<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookRequestSpec extends ObjectBehavior
{
    public function let(UserInterface $user): void
    {
        $user->getUsername()->willReturn('julia');
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->isApiUser()->willReturn(false);

        $author = Author::fromUser($user->getWrappedObject());
        $this->beConstructedWith(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook'),
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                $author,
                'staging.akeneo.com',
                ['data']
            )
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
                    'action' => 'product.created',
                    'event_id' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    'event_date' => '2020-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'pim_source' => 'staging.akeneo.com',
                    'data' => ['data'],
                ]
            );
    }

    public function it_returns_the_webhook(ActiveWebhook $webhook, WebhookEvent $webhookEvent): void
    {
        $this->beConstructedWith(
            $webhook,
            $webhookEvent
        );

        $this->webhook()->shouldReturn($webhook);
    }

    public function it_returns_the_event(ActiveWebhook $webhook, WebhookEvent $webhookEvent): void
    {
        $this->beConstructedWith(
            $webhook,
            $webhookEvent
        );

        $this->event()->shouldReturn($webhookEvent);
    }
}
