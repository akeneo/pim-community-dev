<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Service;

/**
 * The following class is introduced to ease testability of classes that might require it.
 * As the set limit is relatively high, without it integration tests would create a great amount
 * of connections, dampening tests performance
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectionsNumberLimit
{
    public function __construct(private int $connectionsNumberLimit)
    {
    }

    public function getLimit(): int
    {
        return $this->connectionsNumberLimit;
    }

    public function setLimit(int $limit): void
    {
        $this->connectionsNumberLimit = $limit;
    }
}
