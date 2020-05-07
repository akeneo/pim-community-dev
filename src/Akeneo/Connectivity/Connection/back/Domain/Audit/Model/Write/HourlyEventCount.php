<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyEventCount
{
    /** @var string */
    private $connectionCode;

    /** @var HourlyInterval */
    private $hourlyInterval;

    /** @var int */
    private $eventCount;

    /** @var string */
    private $eventType;

    public function __construct(
        string $connectionCode,
        HourlyInterval $hourlyInterval,
        int $eventCount,
        string $eventType
    ) {
        $this->connectionCode = $connectionCode;
        $this->hourlyInterval = $hourlyInterval;
        $this->eventCount = $eventCount;
        $this->eventType = $eventType;
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
