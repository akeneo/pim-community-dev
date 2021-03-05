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
     * This query returns debug logs of the Events API, with a pagination of 25 logs by request.
     * If you need to access the next results, you can simply pass the last "search_after" received as a parameter
     * to the following call.
     *
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
