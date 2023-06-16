<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsHandler
{
    public function __construct(private SelectLastConnectionBusinessErrorsQueryInterface $selectLastConnectionBusinessErrorsQuery)
    {
    }

    /**
     * @return BusinessError[]
     */
    public function handle(GetConnectionBusinessErrorsQuery $query): array
    {
        return $this->selectLastConnectionBusinessErrorsQuery->execute($query->connectionCode(), $query->endDate());
    }
}
