<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     *
     * @return array<WebhookEvent>
     */
    public function build(object $event, array $context = []): array
    {
        $context = $this->resolveOptions($context);

        $eventDataBuilder = $this->getEventDataBuilder($event);

        $eventDataCollection = $eventDataBuilder->build($event, $context['user']);

        $events = [];
        if ($event instanceof EventInterface) {
            $events = [$event];
        } else {
            $events = $event->getEvents();
        }

        return $this->buildWebhookEvents($events, $eventDataCollection, $context);
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['user', 'pim_source']);
        $resolver->setAllowedTypes('user', UserInterface::class);
        $resolver->setAllowedTypes('pim_source', 'string');

        return $resolver->resolve($options);
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

    /**
     * @param array<EventInterface> $events
     * @param array<mixed> $context
     *
     * @return array<WebhookEvent>
     */
    private function buildWebhookEvents(array $events, EventDataCollection $eventDataCollection, array $context): array
    {
        $webhookEvents = [];

        foreach ($events as $event) {
            $data = $eventDataCollection->getEventData($event);

            if (null === $data) {
                // TODO: Log event data not built.

                continue;
            }

            if ($data instanceof \Throwable) {
                // TODO: Log error.

                continue;
            }

            $webhookEvents[] = new WebhookEvent(
                $event->getName(),
                $event->getUuid(),
                date(\DateTimeInterface::ATOM, $event->getTimestamp()),
                $event->getAuthor(),
                $context['pim_source'],
                $data,
            );
        }

        return $webhookEvents;
    }
}
