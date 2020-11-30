<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionDataBuildErrorLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Psr\Log\LoggerInterface;
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

    private LoggerInterface $logger;

    /**
     * @param iterable<EventDataBuilderInterface> $eventDataBuilders
     * @param LoggerInterface $logger
     */
    public function __construct(iterable $eventDataBuilders, LoggerInterface $logger)
    {
        $this->eventDataBuilders = $eventDataBuilders;
        $this->logger = $logger;
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
        $webhookEvents = [];

        // TODO: Remove try/catch when BulkEvent refactoring is completed
        try {
            $eventDataCollection = $eventDataBuilder->build($event, $context['user']);
            $events = $event instanceof EventInterface ? [$event] : $event->getEvents();
            $webhookEvents = $this->buildWebhookEvents($events, $eventDataCollection, $context);
        } catch (EventBuildingExceptionInterface $exception) {
            $this->logger->warning(
                json_encode(
                    (new EventSubscriptionDataBuildErrorLog(
                        $exception->getMessage(),
                        $context['connection_code'],
                        $context['user']->getId(),
                        $event
                    ))->toLog(),
                    JSON_THROW_ON_ERROR
                )
            );
        }

        return $webhookEvents;
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['user', 'pim_source', 'connection_code']);
        $resolver->setAllowedTypes('user', UserInterface::class);
        $resolver->setAllowedTypes('pim_source', 'string');
        $resolver->setAllowedTypes('connection_code', 'string');

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
                throw new \LogicException(sprintf('Event %s should have event data', $event->getUuid()));
            }

            if ($data instanceof \Throwable) {
                $this->logger->warning(
                    json_encode(
                        (new EventSubscriptionDataBuildErrorLog(
                            $data->getMessage(),
                            $context['connection_code'],
                            $context['user']->getId(),
                            $event
                        ))->toLog(),
                        JSON_THROW_ON_ERROR
                    )
                );

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
