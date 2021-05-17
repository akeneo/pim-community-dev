<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionQuery
{
    private string $eventType;

    private DateTimePeriod $period;

    public function __construct(
        string $eventType,
        DateTimePeriod $period
    ) {
        $this->eventType = $eventType;
        $this->period = $period;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function period(): DateTimePeriod
    {
        return $this->period;
    }
}
