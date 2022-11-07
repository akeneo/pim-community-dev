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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetCodesByAssetFamilyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class SqlFindAssetCodesByAssetFamily implements FindAssetCodesByAssetFamilyInterface
{
    private const BATCH_SIZE = 1000;

    public function __construct(private Connection $connection)
    {
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        $searchAfterCode = null;

        $query = <<<SQL
           SELECT code
           FROM akeneo_asset_manager_asset
           WHERE asset_family_identifier = :asset_family_identifier
           %s
           ORDER BY code
           LIMIT :search_after_limit;
        SQL;

        while (true) {
            $sql = $searchAfterCode === null ?
                sprintf($query, '') :
                sprintf($query, 'AND code > :search_after_code');

            $result = $this->connection->executeQuery(
                $sql,
                [
                    'asset_family_identifier' => (string) $assetFamilyIdentifier,
                    'search_after_code' => $searchAfterCode,
                    'search_after_limit' => self::BATCH_SIZE,
                ],
                [
                    'search_after_limit' => ParameterType::INTEGER,
                ]
            );

            while (($code = $result->fetchOne()) !== false) {
                yield AssetCode::fromString($code);
                $searchAfterCode = $code;
            }

            if ($result->rowCount() < self::BATCH_SIZE) {
                break;
            }
        }
    }
}
