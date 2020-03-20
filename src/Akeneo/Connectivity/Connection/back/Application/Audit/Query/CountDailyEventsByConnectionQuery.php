<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionQuery
{
    /** @var string */
    private $eventType;

    /** @var string */
    private $startDate;

    /** @var string */
    private $endDate;

    /** @var string */
    private $timezone;

    public function __construct(
        string $eventType,
        string $startDate,
        string $endDate,
        string $timezone
    ) {
        $this->eventType = $eventType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->timezone = $timezone;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function startDate(): string
    {
        return $this->startDate;
    }

    public function endDate(): string
    {
        return $this->endDate;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }
}
