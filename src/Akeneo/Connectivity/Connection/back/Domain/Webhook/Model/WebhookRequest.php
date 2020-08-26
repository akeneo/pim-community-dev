<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookRequest
{
    /** @var ConnectionWebhook */
    private $webhook;

    /** @var WebhookEvent */
    private $event;

    public function __construct(ConnectionWebhook $webhook, WebhookEvent $event)
    {
        $this->webhook = $webhook;
        $this->event = $event;
    }

    public function webhook(): ConnectionWebhook
    {
        return $this->webhook;
    }

    public function event(): WebhookEvent
    {
        return $this->event;
    }
}
