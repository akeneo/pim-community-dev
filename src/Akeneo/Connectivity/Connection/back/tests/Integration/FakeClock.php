<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration;

use Akeneo\Connectivity\Connection\Domain\Clock;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeClock implements Clock
{
    private ?\DateTimeImmutable $now = null;

    public function setNow(\DateTimeImmutable $now): void
    {
        $this->now = $now;
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}

