<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Domain\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventCountByApp
{
    /** @var string */
    private $appCode;

    /** @var string */
    private $eventType;

    /** @var int */
    private $eventCount;

    /** @var \Datetime */
    private $eventDate;

    public function __construct(string $appCode, string $eventType, int $eventCount, \DateTime $eventDate)
    {
        $this->appCode = $appCode;
        $this->eventType = $eventType;
        $this->eventCount = $eventCount;
        $this->eventDate = $eventDate;
    }

    public function normalize()
    {
        return [
            'app_code' => $this->appCode,
            'event_type' => $this->eventType,
            'event_count' => $this->eventCount,
            'event_date' => $this->eventDate->format('Y-m-d'),
        ];
    }
}
