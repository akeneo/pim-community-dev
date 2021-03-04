<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchEventSubscriptionDebugLogsQueryInterface
{
    /**
     * @return array{
     *  results: array<array{
     *    timestamp: int,
     *    level: string,
     *    message: string,
     *    connection_code: ?string,
     *    context: array
     *  }>,
     *  total: int,
     *  search_after: string
     *}>
     */
    public function execute(string $connectionCode, ?string $encryptedSearchAfter = null): array;
}
