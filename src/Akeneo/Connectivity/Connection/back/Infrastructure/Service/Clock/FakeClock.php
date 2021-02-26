<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\Clock;

use Akeneo\Connectivity\Connection\Domain\Clock;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FakeClock implements Clock
{
    private \DateTimeImmutable $now;

    public function __construct(\DateTimeImmutable $now = null)
    {
        $this->now = $now ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC'));;
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function setNow(\DateTimeImmutable $now): void
    {
        $this->now = $now;
    }
}
