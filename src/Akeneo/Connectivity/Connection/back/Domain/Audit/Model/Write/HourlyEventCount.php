<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyEventCount
{
    public function __construct(private string $connectionCode, private HourlyInterval $hourlyInterval, private int $eventCount, private string $eventType)
    {
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function hourlyInterval(): HourlyInterval
    {
        return $this->hourlyInterval;
    }

    public function eventCount(): int
    {
        return $this->eventCount;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }
}
