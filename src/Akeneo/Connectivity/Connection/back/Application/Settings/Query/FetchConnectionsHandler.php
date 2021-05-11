<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsHandler
{
    private SelectConnectionsQuery $selectConnectionsQuery;

    public function __construct(SelectConnectionsQuery $selectConnectionsQuery)
    {
        $this->selectConnectionsQuery = $selectConnectionsQuery;
    }

    /**
     * @return Connection[]
     */
    public function query(): array
    {
        return $this->selectConnectionsQuery->execute();
    }
}
