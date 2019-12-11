<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Audit\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AppEventCounts
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
        $eventCounts = [];
        foreach ($this->dailyEventCounts as $eventCount) {
            $eventCounts[] = $eventCount->normalize();
        }

        return [
            $this->appCode => $eventCounts
        ];
    }
}
