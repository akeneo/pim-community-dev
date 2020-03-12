<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SelectConnectionsEventCountByDayQuery
{
    /**
     * Return normalized data of hourly event counts per connection and the sum for all connections (<all>).
     *
     * Type:
     * {
     *   '<all>': Array<[DateTime, int]>,
     *   [connectionCode: string]: Array<[DateTime, int]>
     * }
     */
    public function execute(
        string $eventType,
        \DateTimeInterface $fromDateTime,
        \DateTimeInterface $upToDateTime
    ): array;
}
