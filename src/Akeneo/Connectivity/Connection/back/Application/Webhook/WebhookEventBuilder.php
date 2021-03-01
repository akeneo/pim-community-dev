<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionDataBuildErrorLog;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
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
     * @param array<mixed> $context
     *
     * @return array<WebhookEvent>
     */
    public function build(BulkEventInterface $pimEventBulk, array $context = []): array
    {
        $context = $this->resolveOptions($context);
        $eventDataBuilder = $this->getEventDataBuilder($pimEventBulk);

        $eventDataCollection = $eventDataBuilder->build(
            $pimEventBulk,
            $context['user']
        );

        $apiEvents = $this->buildWebhookEvents(
            $pimEventBulk->getEvents(),
            $eventDataCollection,
            $context
        );

        return $apiEvents;
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

    private function getEventDataBuilder(BulkEventInterface $event): EventDataBuilderInterface
    {
        foreach ($this->eventDataBuilders as $builder) {
            if (true === $builder->supports($event)) {
                return $builder;
            }
        }

        throw new WebhookEventDataBuilderNotFoundException($event);
    }

    /**
     * @param array<EventInterface> $pimEvents
     * @param array<mixed> $context
     *
     * @return array<WebhookEvent>
     */
    private function buildWebhookEvents(
        array $pimEvents,
        EventDataCollection $eventDataCollection,
        array $context
    ): array {
        $apiEvents = [];

        foreach ($pimEvents as $pimEvent) {
            $data = $eventDataCollection->getEventData($pimEvent);

            if (null === $data) {
                throw new \LogicException(sprintf('Event %s should have event data', $pimEvent->getUuid()));
            }

            if ($data instanceof \Throwable) {
                $this->logger->warning(
                    json_encode(
                        (new EventSubscriptionDataBuildErrorLog(
                            $data->getMessage(),
                            $context['connection_code'],
                            $context['user']->getId(),
                            $pimEvent
                        ))->toLog(),
                        JSON_THROW_ON_ERROR
                    )
                );

                continue;
            }

            $apiEvents[] = new WebhookEvent(
                $pimEvent->getName(),
                $pimEvent->getUuid(),
                date(\DateTimeInterface::ATOM, $pimEvent->getTimestamp()),
                $pimEvent->getAuthor(),
                $context['pim_source'],
                $data,
            );
        }

        return $apiEvents;
    }
}
