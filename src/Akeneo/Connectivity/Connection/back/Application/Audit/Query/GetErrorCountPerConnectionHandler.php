<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectErrorCountPerConnectionQuery;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionHandler
{
    /** @var SelectErrorCountPerConnectionQuery */
    private $selectErrorCountByConnectionQuery;

    public function __construct(SelectErrorCountPerConnectionQuery $selectErrorCountByConnectionQuery)
    {
        $this->selectErrorCountByConnectionQuery = $selectErrorCountByConnectionQuery;
    }

    /**
     * @return ErrorCountPerConnection[]
     */
    public function handle(GetErrorCountPerConnectionQuery $query): array
    {
        $errorCountsByConnection = $this
            ->selectErrorCountByConnectionQuery
            ->execute($query->eventType(), $query->fromDateTime(), $query->upToDateTime());

        return $errorCountsByConnection;
    }
}
