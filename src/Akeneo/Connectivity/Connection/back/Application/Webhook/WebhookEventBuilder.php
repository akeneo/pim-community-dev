<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilder
{
    /** @var iterable<EventDataBuilderInterface> */
    private $builders;

    /**
     * @param iterable<EventDataBuilderInterface> $builders
     */
    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @param array<mixed> $context
     */
    public function build(BusinessEventInterface $businessEvent, array $context = []): WebhookEvent
    {
        if (!array_key_exists('pim_source', $context)
            || null === $context['pim_source'] || '' === $context['pim_source']
        ) {
            throw new \InvalidArgumentException("Context property 'pim_source' is mandatory");
        }

        if (!array_key_exists('user_id',$context)
            || null === $context['user_id'] || '' === $context['user_id']
        ) {
            throw new \InvalidArgumentException("Context property 'user_id' is mandatory");
        }

        return new WebhookEvent(
            $businessEvent->name(),
            $businessEvent->uuid(),
            date(\DateTimeInterface::ATOM, $businessEvent->timestamp()),
            $businessEvent->author(),
            $context['pim_source'],
            $this->buildEventData($businessEvent, $context['user_id'])
        );
    }

    /**
     * @return array<mixed>
     */
    private function buildEventData(BusinessEventInterface $businessEvent, int $userId): array
    {
        foreach ($this->builders as $builder) {
            if (true === $builder->supports($businessEvent)) {
                return $builder->build($businessEvent, $userId);
            }
        }

        throw new WebhookEventDataBuilderNotFoundException($businessEvent->name());
    }
}
