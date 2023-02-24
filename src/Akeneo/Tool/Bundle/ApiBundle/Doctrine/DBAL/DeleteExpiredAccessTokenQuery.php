<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteExpiredAccessTokenQuery
{
    /**
     * 50 million (10000 * 5000) is an arbitrary choice that allows expired tokens to be removed in less than 24 hours.
     */
    private const DEFAULT_BATCH_SIZE = 10_000;
    private const NUMBER_OF_LOOP = 5_000;
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(): void
    {
        $nowTimestamp = \time();
        $batchSize = self::DEFAULT_BATCH_SIZE;

        $statement = $this->connection->prepare(<<<SQL
            DELETE FROM pim_api_access_token
            WHERE expires_at < :now_timestamp
            LIMIT :row_count;
        SQL);

        $statement->bindValue('row_count', $batchSize, ParameterType::INTEGER);
        $statement->bindValue('now_timestamp', $nowTimestamp, ParameterType::INTEGER);


        for ($i = 0; $i < self::NUMBER_OF_LOOP; $i++) {
            $affectedRows = $statement->executeStatement();
            if ($affectedRows < $batchSize) {
                break;
            }
        }
    }
}
