<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodEventCountPerConnectionQueryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionHandler
{
    public function __construct(private SelectPeriodEventCountPerConnectionQueryInterface $selectPeriodEventCountPerConnectionQuery)
    {
    }

    /**
     * @return PeriodEventCount[]
     */
    public function handle(GetPeriodEventCountPerConnectionQuery $query): array
    {
        return $this
            ->selectPeriodEventCountPerConnectionQuery
            ->execute($query->eventType(), $query->period());
    }
}
