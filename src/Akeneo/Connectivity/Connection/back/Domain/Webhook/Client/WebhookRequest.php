<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequest
{
    /** @var ActiveWebhook */
    private $webhook;

    /** @var WebhookEvent */
    private $event;

    public function __construct(ActiveWebhook $webhook, WebhookEvent $event)
    {
        $this->webhook = $webhook;
        $this->event = $event;
    }

    /**
     * Returns webhook URL.
     */
    public function url(): string
    {
        return $this->webhook->url();
    }

    /**
     * Returns webhook secret to sign the request.
     */
    public function secret(): string
    {
        return $this->webhook->secret();
    }

    /**
     * Returns request content.
     *
     * @return array{
     *  action: string,
     *  event_id: string,
     *  event_date: string,
     *  author: string,
     *  author_type: string,
     *  pim_source: string,
     *  data: array
     * }
     */
    public function content(): array
    {
        return [
            'action' => $this->event->action(),
            'event_id' => $this->event->eventId(),
            'event_date' => $this->event->eventDate(),
            'author' => $this->event->author()->name(),
            'author_type' => $this->event->author()->type(),
            'pim_source' => $this->event->pimSource(),
            'data' => $this->event->data(),
        ];
    }

    public function webhook(): ActiveWebhook
    {
        return $this->webhook;
    }

    public function event(): WebhookEvent
    {
        return $this->event;
    }
}
