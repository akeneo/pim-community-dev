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
    private $appCode;

    /** @var string */
    private $eventType;

    /** @var array */
    private $eventCounts;

    public function __construct(string $appCode, string $eventType, array $eventCounts)
    {
        $this->appCode = $appCode;
        $this->eventType = $eventType;
        $this->eventCounts = $eventCounts;
    }

    public function normalize()
    {
        return [
            'app_code' => $this->appCode,
            'event_type' => $this->eventType,
        ];
    }
}
