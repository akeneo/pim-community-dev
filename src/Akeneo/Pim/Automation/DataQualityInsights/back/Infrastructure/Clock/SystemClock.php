<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
