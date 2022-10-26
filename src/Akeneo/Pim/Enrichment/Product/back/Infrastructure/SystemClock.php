<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure;

use Akeneo\Pim\Enrichment\Product\Domain\Clock;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
