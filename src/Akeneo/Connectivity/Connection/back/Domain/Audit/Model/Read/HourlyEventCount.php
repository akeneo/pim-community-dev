<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class HourlyEventCount
{
    /** @var \DateTimeImmutable */
    private $dateTime;

    /** @var int */
    private $count;

    public function __construct(\DateTimeImmutable $dateTime, int $count)
    {
        $this->dateTime = $dateTime;
        $this->count = $count;
    }

    public function dateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function count(): int
    {
        return $this->count;
    }
}
