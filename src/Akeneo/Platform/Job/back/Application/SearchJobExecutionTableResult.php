<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application;

use Webmozart\Assert\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecutionTableResult
{
    private const ITEMS = 'items';
    private const MATCHES_COUNT = 'matches_count';
    private const TOTAL_COUNT = 'total_count';

    /** @var JobExecutionRow[] */
    public array $items;
    public int $matchesCount;
    public int $totalCount;

    public function __construct(
        array $jobItems,
        int $matchesCount,
        int $totalCount
    ) {
        Assert::allIsInstanceOf($jobItems, JobExecutionRow::class);
        $this->items = $jobItems;
        $this->matchesCount = $matchesCount;
        $this->totalCount = $totalCount;
    }

    public function normalize(): array
    {
        return [
            self::ITEMS => array_map(static fn (JobExecutionRow $jobItem) => $jobItem->normalize(), $this->items),
            self::MATCHES_COUNT => $this->matchesCount,
            self::TOTAL_COUNT => $this->totalCount,
        ];
    }
}
