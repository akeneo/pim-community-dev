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
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlFindAssetIdentifiersByAssetFamily implements FindAssetIdentifiersByAssetFamilyInterface
{
    private const BATCH_SIZE = 1000;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        $searchAfterIdentifier = null;

        $query = <<<SQL
           SELECT identifier
           FROM akeneo_asset_manager_asset
           WHERE asset_family_identifier = :asset_family_identifier
           %s
           ORDER BY identifier
           LIMIT :search_after_limit;
SQL;

        while (true) {
            $sql = $searchAfterIdentifier === null ?
                sprintf($query, '') :
                sprintf($query, 'AND identifier > :search_after_identifier');

            $statement = $this->connection->executeQuery(
                $sql,
                [
                    'asset_family_identifier' => (string) $assetFamilyIdentifier,
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

            $platform = $this->connection->getDatabasePlatform();
            while (false !== $identifier = $statement->fetchColumn()) {
                $stringIdentifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);

                yield AssetIdentifier::fromString($stringIdentifier);
                $searchAfterIdentifier = $identifier;
            }
        }
    }
}
