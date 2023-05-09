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
    public function __construct(private string $eventType, private DateTimePeriod $period)
    {
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
