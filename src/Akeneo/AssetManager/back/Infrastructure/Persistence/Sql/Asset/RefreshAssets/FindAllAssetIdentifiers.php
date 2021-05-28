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
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(): \Iterator
    {
        $query = <<<SQL
SELECT identifier FROM akeneo_asset_manager_asset;
SQL;
        $statement = $this->sqlConnection->executeQuery($query);

        while (false !== $result = $statement->fetch(\PDO::FETCH_COLUMN)) {
            yield AssetIdentifier::fromString($result);
        }
    }
}
