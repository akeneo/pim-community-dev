<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;

final class SystemClock implements Clock
{
    private $timeZone;

    public function __construct()
    {
        $this->timeZone = new \DateTimeZone('UTC');
    }

    public function getCurrentTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', $this->timeZone);
    }

    public function fromString(string $date): \DateTimeImmutable
    {
        return new \DateTimeImmutable($date, $this->timeZone);
    }

    public function fromTimestamp(int $timestamp): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }
}
