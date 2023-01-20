<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteExpiredRefreshTokenQuery
{
    private const DEFAULT_BATCH_SIZE = 100_000;
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): void
    {
        $nowTimestamp = \time();
        $batchSize = self::DEFAULT_BATCH_SIZE;

        $statement = $this->connection->prepare(<<<SQL
            DELETE FROM pim_api_refresh_token
            WHERE expires_at < :now_timestamp
            LIMIT :row_count;
        SQL);

        $statement->bindValue('row_count', $batchSize, ParameterType::INTEGER);
        $statement->bindValue('now_timestamp', $nowTimestamp, ParameterType::INTEGER);

        do {
            $affectedRows = $statement->executeStatement();
        } while ($affectedRows >= $batchSize);
    }
}
