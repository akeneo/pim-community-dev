<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllAssetIdentifiers implements SelectAssetIdentifiersInterface
{
    private const BATCH_SIZE = 1000;

    public function __construct(private Connection $sqlConnection)
    {
    }

    public function fetch(): \Iterator
    {
        $searchAfterIdentifier = null;

        $query = <<<SQL
           SELECT identifier
           FROM akeneo_asset_manager_asset
           %s
           ORDER BY identifier
           LIMIT :search_after_limit;
SQL;

        while (true) {
            $sql = $searchAfterIdentifier === null ?
                sprintf($query, '') :
                sprintf($query, 'WHERE identifier > :search_after_identifier');

            $statement = $this->sqlConnection->executeQuery(
                $sql,
                [
                    'search_after_identifier' => $searchAfterIdentifier,
                    'search_after_limit' => self::BATCH_SIZE
                ],
                [
                    'search_after_limit' => \PDO::PARAM_INT
                ]
            );

            if ($statement->rowCount() === 0) {
                return;
            }

            while (false !== $result = $statement->fetchOne()) {
                yield AssetIdentifier::fromString($result);
                $searchAfterIdentifier = $result;
            }
        }
    }
}
