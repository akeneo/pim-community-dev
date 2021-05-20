<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindIdentifiersByAssetFamilyAndCodes implements FindIdentifiersByAssetFamilyAndCodesInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $query = <<<SQL
        SELECT identifier, code
        FROM akeneo_asset_manager_asset
        WHERE asset_family_identifier = :assetFamilyIdentifier
        AND code IN (:codes)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'codes' => $assetCodes
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );


        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $identifiers = [];
        foreach ($results as $result) {
            $identifiers[$result['code']] = AssetIdentifier::fromString($result['identifier']);
        }

        return $identifiers;
    }
}
