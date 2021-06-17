<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamilyPermission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\AssetFamilyPermission;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupPermission;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;

class SqlAssetFamilyPermissionRepository implements AssetFamilyPermissionRepositoryInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function save(AssetFamilyPermission $assetFamilyPermission): void
    {
        $userPermissions = $assetFamilyPermission->getPermissions();
        $assetFamilyIdentifier = $assetFamilyPermission->getAssetFamilyIdentifier()->normalize();

        $normalizedPermissions = [];
        foreach ($userPermissions as $userPermission) {
            $normalizedPermission = $userPermission->normalize();
            $normalizedPermission['asset_family_identifier'] = $assetFamilyIdentifier;

            $normalizedPermissions[] = $normalizedPermission;
        }

        $this->sqlConnection->transactional(function (Connection $connection) use ($normalizedPermissions, $assetFamilyIdentifier) {
            $deleteSql = <<<SQL
                DELETE FROM akeneo_asset_manager_asset_family_permissions
                WHERE asset_family_identifier = :asset_family_identifier;
SQL;
            $connection->executeUpdate(
                $deleteSql,
                [
                    'asset_family_identifier' => $assetFamilyIdentifier
                ]
            );

            foreach ($normalizedPermissions as $normalizedPermission) {
                $insertSql = <<<SQL
                    INSERT INTO akeneo_asset_manager_asset_family_permissions
                        (asset_family_identifier, user_group_identifier, right_level)
                    VALUES 
                        (:asset_family_identifier, :user_group_identifier, :right_level);
SQL;
                $affectedRows = $this->sqlConnection->executeUpdate(
                    $insertSql,
                    $normalizedPermission
                );
                if ($affectedRows === 0) {
                    throw new \RuntimeException('Expected to create asset family permissions, but none were inserted.');
                }
            }
        });
    }

    public function getByAssetFamilyIdentifier(
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): AssetFamilyPermission {
        $fetch = <<<SQL
        SELECT asset_family_identifier, user_group_identifier, right_level
        FROM akeneo_asset_manager_asset_family_permissions
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['asset_family_identifier' => (string) $assetFamilyIdentifier]
        );

        $userGroupPermissions = $this->hydrateUserGroupPermissions($statement);

        return AssetFamilyPermission::create($assetFamilyIdentifier, $userGroupPermissions);
    }

    /**
     * @return UserGroupPermission[]
     */
    private function hydrateUserGroupPermissions(Statement $statement): array
    {
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $platform = $this->sqlConnection->getDatabasePlatform();

        if (false !== $result) {
            return array_map(
                function (array $normalizedUserGroupPermission) use ($platform) {
                    $userGroupIdentifier = Type::getType(Type::INTEGER)
                        ->convertToPhpValue($normalizedUserGroupPermission['user_group_identifier'], $platform);
                    $rightLevel = Type::getType(Type::STRING)
                        ->convertToPhpValue($normalizedUserGroupPermission['right_level'], $platform);

                    return UserGroupPermission::create(
                        UserGroupIdentifier::fromInteger($userGroupIdentifier),
                        RightLevel::fromString($rightLevel)
                    );
                },
                $result
            );
        }

        return [];
    }
}
