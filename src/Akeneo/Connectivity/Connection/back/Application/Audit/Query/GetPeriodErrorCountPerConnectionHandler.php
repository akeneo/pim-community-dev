<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodErrorCountPerConnectionQueryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetPeriodErrorCountPerConnectionHandler
{
    public function __construct(private SelectPeriodErrorCountPerConnectionQueryInterface $selectPeriodErrorCountPerConnectionQuery)
    {
    }

    /**
     * @return PeriodEventCount[]
     */
    public function handle(GetPeriodErrorCountPerConnectionQuery $query): array
    {
        return $this->selectPeriodErrorCountPerConnectionQuery->execute($query->period());
    }
}
