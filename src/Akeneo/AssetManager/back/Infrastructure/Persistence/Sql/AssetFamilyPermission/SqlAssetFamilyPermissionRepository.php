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
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlAssetFamilyPermissionRepository implements AssetFamilyPermissionRepositoryInterface
{
    public function __construct(private Connection $sqlConnection)
    {
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
            $connection->executeStatement(
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
                $affectedRows = $this->sqlConnection->executeStatement(
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
    private function hydrateUserGroupPermissions(Result $result): array
    {
        $rows = $result->fetchAllAssociative();
        $platform = $this->sqlConnection->getDatabasePlatform();

        if (false !== $rows) {
            return array_map(
                function (array $normalizedUserGroupPermission) use ($platform) {
                    $userGroupIdentifier = Type::getType(Types::INTEGER)
                        ->convertToPhpValue($normalizedUserGroupPermission['user_group_identifier'], $platform);
                    $rightLevel = Type::getType(Types::STRING)
                        ->convertToPhpValue($normalizedUserGroupPermission['right_level'], $platform);

                    return UserGroupPermission::create(
                        UserGroupIdentifier::fromInteger($userGroupIdentifier),
                        RightLevel::fromString($rightLevel)
                    );
                },
                $rows
            );
        }

        return [];
    }
}
