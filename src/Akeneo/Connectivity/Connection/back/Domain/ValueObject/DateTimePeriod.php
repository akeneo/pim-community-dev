<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DateTimePeriod
{
    private \DateTimeImmutable $start;

    private \DateTimeImmutable $end;

    public function __construct(\DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        if ('UTC' !== $start->getTimezone()->getName()) {
            throw new \InvalidArgumentException('Parameter $start must have the UTC TimeZone.');
        }
        $this->start = $start;

        if ('UTC' !== $end->getTimezone()->getName()) {
            throw new \InvalidArgumentException('Parameter $end must have the UTC TimeZone.');
        }
        $this->end = $end;
    }

    public function start(): \DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): \DateTimeImmutable
    {
        return $this->end;
    }
}
