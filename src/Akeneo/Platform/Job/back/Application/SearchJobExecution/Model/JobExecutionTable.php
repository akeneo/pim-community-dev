<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Webmozart\Assert\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionTable
{
    private const ROWS = 'rows';
    private const MATCHES_COUNT = 'matches_count';

    public function __construct(
        /** @var JobExecutionRow[] */
        public array $rows,
        public int $matchesCount,
    ) {
        Assert::allIsInstanceOf($rows, JobExecutionRow::class);
    }

    public function normalize(): array
    {
        return [
            self::ROWS => array_map(static fn (JobExecutionRow $jobItem) => $jobItem->normalize(), $this->rows),
            self::MATCHES_COUNT => $this->matchesCount,
        ];
    }
}
