<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\ApiEventBuildErrorLoggerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\Context;
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
    /**
     * @param iterable<EventDataBuilderInterface> $eventDataBuilders
     */
    public function __construct(
        private iterable $eventDataBuilders,
        private ApiEventBuildErrorLoggerInterface $apiEventBuildErrorLogger
    ) {
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

        /** @var UserInterface $user */
        $user = $context['user'];

        $eventDataCollection = $eventDataBuilder->build(
            $pimEventBulk,
            new Context($user->getUserIdentifier(), $user->getId(), $context['is_using_uuid'])
        );

        return $this->buildWebhookEvents(
            $pimEventBulk->getEvents(),
            $eventDataCollection,
            $context
        );
    }

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['user', 'pim_source', 'connection_code', 'is_using_uuid']);
        $resolver->setAllowedTypes('user', UserInterface::class);
        $resolver->setAllowedTypes('pim_source', 'string');
        $resolver->setAllowedTypes('connection_code', 'string');
        $resolver->setAllowedTypes('is_using_uuid', 'bool');

        return $resolver->resolve($options);
    }

    private function getEventDataBuilder(BulkEventInterface $event): EventDataBuilderInterface
    {
        foreach ($this->eventDataBuilders as $builder) {
            if ($builder->supports($event)) {
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
                throw new \LogicException(\sprintf('Event %s should have event data', $pimEvent->getUuid()));
            }

            if ($data instanceof \Throwable) {
                $this->apiEventBuildErrorLogger->logResourceNotFoundOrAccessDenied(
                    $context['connection_code'],
                    $pimEvent
                );

                continue;
            }

            $apiEvents[] = new WebhookEvent(
                $pimEvent->getName(),
                $pimEvent->getUuid(),
                \date(\DateTimeInterface::ATOM, $pimEvent->getTimestamp()),
                $pimEvent->getAuthor(),
                $context['pim_source'],
                $data,
                $pimEvent
            );
        }

        return $apiEvents;
    }
}
