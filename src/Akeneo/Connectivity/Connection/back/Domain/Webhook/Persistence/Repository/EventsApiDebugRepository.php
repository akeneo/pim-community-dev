<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventsApiDebugRepository
{
    /**
     * @param array<array{
     *  timestamp: int,
     *  level: string,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * }> $documents
     */
    public function bulkInsert(array $documents): void;
}
