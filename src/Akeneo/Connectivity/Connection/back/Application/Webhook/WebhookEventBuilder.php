<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilder
{
    /** @var iterable<EventDataBuilderInterface> */
    private iterable $eventDataBuilders;

    /**
     * @param iterable<EventDataBuilderInterface> $eventDataBuilders
     */
    public function __construct(iterable $eventDataBuilders)
    {
        $this->eventDataBuilders = $eventDataBuilders;
    }

    /**
     * @param EventInterface|BulkEventInterface $event
     * @param array<mixed> $context
     * @return array<WebhookEvent>
     */
    public function build(object $event, array $context = []): array
    {
        Assert::notEmpty($context['pim_source'] ?? null);
        Assert::notEmpty($context['user_id'] ?? null);

        $eventDataBuilder = $this->getEventDataBuilder($event);

        if ($event instanceof EventInterface) {
            $data = $eventDataBuilder->build($event, $context['user_id']);

            return [
                new WebhookEvent(
                    $event->getName(),
                    $event->getUuid(),
                    date(\DateTimeInterface::ATOM, $event->getTimestamp()),
                    $event->getAuthor(),
                    $context['pim_source'],
                    $data
                )
            ];
        }

        if ($event instanceof BulkEventInterface) {
            $events = $event->getEvents();
            $eventsData = $eventDataBuilder->build($event, $context['user_id']);

            $webhookEvents = [];
            foreach ($events as $i => $event) {
                $webhookEvents[] = new WebhookEvent(
                    $event->getName(),
                    $event->getUuid(),
                    date(\DateTimeInterface::ATOM, $event->getTimestamp()),
                    $event->getAuthor(),
                    $context['pim_source'],
                    $eventsData[$i]
                );
            }

            return $webhookEvents;
        }
    }

    /**
     * @param EventInterface|BulkEventInterface $event
     */
    private function getEventDataBuilder(object $event): EventDataBuilderInterface
    {
        foreach ($this->eventDataBuilders as $builder) {
            if (true === $builder->supports($event)) {
                return $builder;
            }
        }

        throw new WebhookEventDataBuilderNotFoundException($event);
    }
}
