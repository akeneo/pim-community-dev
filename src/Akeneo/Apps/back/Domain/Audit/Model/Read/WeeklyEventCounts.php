<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Audit\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class WeeklyEventCounts
{
    /** @var string */
    private $appCode;

    /** @var DailyEventCount[] */
    private $dailyEventCounts = [];

    public function __construct(string $appCode)
    {
        $this->appCode = $appCode;
    }

    public function addDailyEventCount(DailyEventCount $dailyEventCount): void
    {
        $this->dailyEventCounts[] = $dailyEventCount;
    }

    public function normalize()
    {
        return [
            $this->appCode => \array_reduce(
                $this->dailyEventCounts,
                function (array $weeklyEventCounts, DailyEventCount $dailyEventCount) {
                    return array_merge($weeklyEventCounts, $dailyEventCount->normalize());
                },
                []
            ),
        ];
    }
}
