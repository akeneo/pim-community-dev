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

    public function build(BusinessEventInterface $businessEvent): WebhookEvent
    {
        return new WebhookEvent(
            $businessEvent->name(),
            $businessEvent->uuid(),
            date(\DateTimeInterface::ATOM, $businessEvent->timestamp()),
            $this->buildEventData($businessEvent)
        );
    }

    /**
     * @return array<mixed>
     */
    private function buildEventData(BusinessEventInterface $businessEvent): array
    {
        foreach ($this->builders as $builder) {
            if (true === $builder->supports($businessEvent)) {
                return $builder->build($businessEvent);
            }
        }

        throw new WebhookEventDataBuilderNotFoundException($businessEvent->name());
    }
}
