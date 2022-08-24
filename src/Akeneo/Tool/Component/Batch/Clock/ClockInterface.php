<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Clock;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
