<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class PeriodEventCount
{
    /**
     * @param HourlyEventCount[] $hourlyEventCounts
     */
    public function __construct(private string $connectionCode, private \DateTimeImmutable $fromDateTime, private \DateTimeImmutable $upToDateTime, private array $hourlyEventCounts)
    {
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }

    public function fromDateTime(): \DateTimeImmutable
    {
        return $this->fromDateTime;
    }

    public function upToDateTime(): \DateTimeImmutable
    {
        return $this->upToDateTime;
    }

    /**
     * @return HourlyEventCount[]
     */
    public function hourlyEventCounts(): array
    {
        return $this->hourlyEventCounts;
    }
}
