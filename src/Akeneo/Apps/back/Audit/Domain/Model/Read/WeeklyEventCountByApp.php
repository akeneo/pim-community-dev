<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Domain\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class WeeklyEventCountByApp
{
    /** @var string */
    private $appLabel;

    /** @var string */
    private $eventType;

    /** @var array */
    private $eventCounts;

    public function __construct(string $appLabel, string $eventType, array $eventCounts)
    {
        $this->appLabel = $appLabel;
        $this->eventType = $eventType;
        $this->eventCounts = $eventCounts;
    }

    public function normalize()
    {
        $eventCounts = [];
        foreach ($this->eventCounts as $eventCount) {
            $eventCounts[$eventCount->date()->format('Y-m-d')] = $eventCount->count();
        }

        return [
            'app_label' => $this->appLabel,
            'event_type' => $this->eventType,
            'event_counts' => $eventCounts,
        ];
    }
}
