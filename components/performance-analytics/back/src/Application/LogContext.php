<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Application;

final class LogContext
{
    /**
     * @param array<string, mixed> $extraContext
     * @return array<string, mixed>
     */
    public static function build(array $extraContext = []): array
    {
        return \array_merge($extraContext, ['bounded_context' => 'performance-analytics']);
    }
}
