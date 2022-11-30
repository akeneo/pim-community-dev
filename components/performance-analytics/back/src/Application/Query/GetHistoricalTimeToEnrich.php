<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Application\Query;

use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichQuery;

final class GetHistoricalTimeToEnrich
{
    public function __construct(
        private readonly AverageTimeToEnrichQuery $query
    ) {
    }

    public function averageTimeToEnrichQuery(): AverageTimeToEnrichQuery
    {
        return $this->query;
    }
}
