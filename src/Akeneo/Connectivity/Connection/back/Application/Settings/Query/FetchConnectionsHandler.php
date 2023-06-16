<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsHandler
{
    public function __construct(private SelectConnectionsQueryInterface $selectConnectionsQuery)
    {
    }

    /**
     * @return Connection[]
     */
    public function handle(FetchConnectionsQuery $fetchConnectionsQuery): array
    {
        $connectionTypes = $fetchConnectionsQuery->getTypes();

        return $this->selectConnectionsQuery->execute($connectionTypes);
    }
}
