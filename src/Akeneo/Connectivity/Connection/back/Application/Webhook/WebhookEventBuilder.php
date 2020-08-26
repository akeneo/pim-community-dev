<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

class WebhookEventBuilder
{
    /** @var WebhookEventBuilderRegistry */
    private $dataBuilderRegistry;

    public function __construct(WebhookEventBuilderRegistry $dataBuilderRegistry)
    {
        $this->dataBuilderRegistry = $dataBuilderRegistry;
    }

    public function build(ConnectionWebhook $webhook, BusinessEventInterface $businessEvent): WebhookEvent
    {
        $date = new \DateTime($businessEvent->getTimestamp(), new \DateTimeZone('UTC'));

        return new WebhookEvent(
            $businessEvent->getName(),
            $businessEvent->getUuid(),
            $date->format(DATE_ATOM),
            $this->dataBuilderRegistry->build($webhook, $businessEvent)
        );
    }
}
