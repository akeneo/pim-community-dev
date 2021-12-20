<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\CountAllConnectionsQueryInterface;

class IsConnectionsNumberLimitReachedQuery
{
    public function __construct(
        private CountAllConnectionsQueryInterface $countAllConnectionsQuery,
        private int $connectionsNumberLimit
    ) {
    }

    public function execute(): bool
    {
        $count = $this->countAllConnectionsQuery->execute();

        return $count >= $this->connectionsNumberLimit;
    }
}
