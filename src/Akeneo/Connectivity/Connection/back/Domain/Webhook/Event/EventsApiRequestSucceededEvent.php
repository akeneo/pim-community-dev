<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Event;

use Akeneo\Platform\Component\EventQueue\EventInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestSucceededEvent
{
    /** @var EventInterface[] */
    private array $events;

    /**
     * @param EventInterface[] $events
     */
    public function __construct(private string $connectionCode, array $events)
    {
        Assert::allIsInstanceOf($events, EventInterface::class);
        $this->events = $events;
    }

    public function getConnectionCode(): string
    {
        return $this->connectionCode;
    }

    /**
     * @return EventInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
