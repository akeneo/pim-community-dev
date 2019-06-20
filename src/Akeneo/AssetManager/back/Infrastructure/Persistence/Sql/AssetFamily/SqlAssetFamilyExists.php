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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetFamilyExists implements AssetFamilyExistsInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): bool
    {
        $statement = $this->executeQuery($assetFamilyIdentifier);

        return $this->isIdentifierExisting($statement);
    }

    private function executeQuery(AssetFamilyIdentifier $assetFamilyIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_asset_family
            WHERE identifier = :identifier 
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['identifier' => (string) $assetFamilyIdentifier]);

        return $statement;
    }

    private function isIdentifierExisting(Statement $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}
