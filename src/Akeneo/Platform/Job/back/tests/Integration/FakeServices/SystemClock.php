<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\FakeServices;

use Akeneo\Platform\Job\Infrastructure\Clock\ClockInterface;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2020-01-02 01:00:00', new \DateTimeZone('Europe/Paris'));
    }
}
