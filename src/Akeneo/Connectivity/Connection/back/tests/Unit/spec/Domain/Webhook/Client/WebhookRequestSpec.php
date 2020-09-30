<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookRequestSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook'),
            new WebhookEvent(
                'product.created',
                '79fc4791-86d6-4d3b-93c5-76b787af9497',
                '2020-01-01T00:00:00+00:00',
                'julia',
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
                    'pim_source' => 'staging.akeneo.com',
                    'data' => ['data'],
                ]
            );
    }

    public function it_returns_metadata(): void
    {
        $this->metadata()
            ->shouldReturn(
                [
                    'webhook' => [
                        'connection_code' => 'ecommerce',
                        'url' => 'http://localhost/webhook',
                    ],
                    'event' => [
                        'action' => 'product.created',
                        'event_id' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                        'event_date' => '2020-01-01T00:00:00+00:00',
                        'author' => 'julia',
                        'pim_source' => 'staging.akeneo.com',
                    ],
                ]
            );
    }
}
