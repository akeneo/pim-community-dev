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
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlFindIdentifiersByAssetFamily implements FindIdentifiersByAssetFamilyInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        $statement = $this->connection->executeQuery(
            'SELECT identifier FROM akeneo_asset_manager_asset WHERE asset_family_identifier = :assetFamilyIdentifier',
            ['assetFamilyIdentifier' => (string)$assetFamilyIdentifier]
        );

        $platform = $this->connection->getDatabasePlatform();
        while (false !== $identifier = $statement->fetchColumn()) {
            $stringIdentifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);

            yield AssetIdentifier::fromString($stringIdentifier);
        }
    }
}
