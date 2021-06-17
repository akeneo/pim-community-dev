<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindExistingAssetCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindExistingAssetCodes implements FindExistingAssetCodesInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $query = <<<SQL
        SELECT code
        FROM akeneo_asset_manager_asset
        WHERE asset_family_identifier = :assetFamilyIdentifier
        AND FIND_IN_SET(code, :codes)
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
            'codes' => implode(',', $assetCodes)
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
