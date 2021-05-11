<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionHandler
{
    private SelectErrorCountPerConnectionQuery $selectErrorCountPerConnectionQuery;

    public function __construct(SelectErrorCountPerConnectionQuery $selectErrorCountPerConnectionQuery)
    {
        $this->selectErrorCountPerConnectionQuery = $selectErrorCountPerConnectionQuery;
    }

    public function handle(GetErrorCountPerConnectionQuery $query): ErrorCountPerConnection
    {
        return $this
            ->selectErrorCountPerConnectionQuery
            ->execute(new ErrorType($query->errorType()), $query->fromDateTime(), $query->upToDateTime());
    }
}
