<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectPeriodErrorCountPerConnectionQueryInterface
{
    /**
     * @return PeriodEventCount[]
     */
    public function execute(DateTimePeriod $period): array;
}
