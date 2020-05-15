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
    private $selectErrorCountPerConnectionQuery;

    public function __construct(SelectErrorCountPerConnectionQuery $selectErrorCountPerConnectionQuery)
    {
        $this->selectErrorCountPerConnectionQuery = $selectErrorCountPerConnectionQuery;
    }

    public function handle(GetErrorCountPerConnectionQuery $query): ErrorCountPerConnection
    {
        $errorCountsPerConnection = $this
            ->selectErrorCountPerConnectionQuery
            ->execute($query->eventType(), $query->fromDateTime(), $query->upToDateTime());

        return $errorCountsPerConnection;
    }
}
