<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQuery;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsHandler
{
    private SelectLastConnectionBusinessErrorsQuery $selectLastConnectionBusinessErrorsQuery;

    public function __construct(SelectLastConnectionBusinessErrorsQuery $selectLastConnectionBusinessErrorsQuery)
    {
        $this->selectLastConnectionBusinessErrorsQuery = $selectLastConnectionBusinessErrorsQuery;
    }

    /**
     * @return BusinessError[]
     */
    public function handle(GetConnectionBusinessErrorsQuery $query): array
    {
        return $this->selectLastConnectionBusinessErrorsQuery->execute($query->connectionCode(), $query->endDate());
    }
}
