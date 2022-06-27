<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkEvent implements BulkEventInterface
{
    private array $events;
    private ?string $tenantId = null;

    /**
     * @param array<EventInterface> $events
     */
    public function __construct(array $events)
    {
        Assert::allIsInstanceOf($events, Event::class);

        $this->events = $events;
    }

    /**
     * @return array<EventInterface>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
}
