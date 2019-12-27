<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Write;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DailyEventCount
{
    /** @var string */
    private $connectionCode;
    /** @var string */
    private $eventDate;
    /** @var int */
    private $eventCount;
    /** @var string */
    private $eventType;

    public function __construct(string $connectionCode, string $eventDate, int $eventCount, string $eventType)
    {
        $this->connectionCode = $connectionCode;
        $this->eventDate = $eventDate;
        $this->eventCount = $eventCount;
        $this->eventType = $eventType;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function eventDate():  string
    {
        return $this->eventDate;
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
