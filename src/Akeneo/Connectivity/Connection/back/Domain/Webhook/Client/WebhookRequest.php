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
            'author' => $this->event->author(),
            'author_type' => $this->event->authorType(),
            'pim_source' => $this->event->pimSource(),
            'data' => $this->event->data(),
        ];
    }

    /**
     * Returns request metadata such as webhook & event details, but not the event data.
     *
     * @return array{
     *  webhook: array{
     *      connection_code: string,
     *      url: string
     *  },
     *  event: array{
     *      action: string,
     *      event_id: string,
     *      event_date: string,
     *      author: string,
     *      author_type: string,
     *      pim_source: string
     *  }
     * }
     */
    public function metadata(): array
    {
        return [
            'webhook' => [
                'connection_code' => $this->webhook->connectionCode(),
                'url' => $this->webhook->url(),
            ],
            'event' => [
                'action' => $this->event->action(),
                'event_id' => $this->event->eventId(),
                'event_date' => $this->event->eventDate(),
                'author' => $this->event->author(),
                'author_type' => $this->event->authorType(),
                'pim_source' => $this->event->pimSource(),
            ],
        ];
    }
}
