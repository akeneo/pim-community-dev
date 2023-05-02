<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetPeriodErrorCountPerConnectionQuery
{
    public function __construct(private DateTimePeriod $period)
    {
    }

    public function period(): DateTimePeriod
    {
        return $this->period;
    }
}
