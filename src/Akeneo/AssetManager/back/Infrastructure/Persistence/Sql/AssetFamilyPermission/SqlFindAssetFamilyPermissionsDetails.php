<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamilyPermission;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\FindAssetFamilyPermissionsDetailsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\PermissionDetails;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * If there are no permissions set for the asset family, then this query function returns an empty list.
 * However, if there are permissions, we need to merge those with all the user group (except 'All') defined in the PIM
 * with the default right level: "view"
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetFamilyPermissionsDetails implements FindAssetFamilyPermissionsDetailsInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @return PermissionDetails[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $userGroups = $this->fetchUserGroups();
        $permissionDetails = $this->fetchPermissions($assetFamilyIdentifier);

        return $this->hydrate($userGroups, $permissionDetails);
    }

    private function fetchUserGroups(): array
    {
        $query = <<<SQL
SELECT ug.id as user_group_identifier, ug.name as user_group_name
FROM oro_access_group ug
WHERE ug.name <> 'All';
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return false !== $result ? $result : [];
    }

    private function fetchPermissions(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $query = <<<SQL
SELECT ug.id as user_group_identifier, ug.name as user_group_name, rp.right_level
FROM oro_access_group ug INNER JOIN akeneo_asset_manager_asset_family_permissions rp ON ug.id = rp.user_group_identifier
WHERE rp.asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['asset_family_identifier' => (string) $assetFamilyIdentifier]
        );
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return false !== $result ? $result : [];
    }

    /**
     * @return PermissionDetails[]
     */
    private function hydrate(array $userGroups, array $normalizedpermissionDetails): array
    {
        return array_map(
            function (array $userGroup) use ($normalizedpermissionDetails) {
                $rightLevelForGroup = $this->getRightLevel($userGroup, $normalizedpermissionDetails);

                return $this->createPermissionDetails($userGroup, $rightLevelForGroup);
            },
            $userGroups
        );
    }

    private function getRightLevel(array $userGroup, array $normalizedPermissionDetails): string
    {
        $rightLevel = empty($normalizedPermissionDetails) ? RightLevel::EDIT : RightLevel::VIEW;

        $platform = $this->sqlConnection->getDatabasePlatform();
        $userGroupIdentifier = Type::getType(Type::INTEGER)
            ->convertToPhpValue($userGroup['user_group_identifier'], $platform);

        foreach ($normalizedPermissionDetails as $normalizedPermissionDetail) {
            $searchedUserGroup = Type::getType(Type::INTEGER)
                ->convertToPhpValue($normalizedPermissionDetail['user_group_identifier'], $platform);
            if ($userGroupIdentifier === $searchedUserGroup) {
                $rightLevel = Type::getType(Type::STRING)
                    ->convertToPhpValue($normalizedPermissionDetail['right_level'], $platform);
            }
        }

        return $rightLevel;
    }

    private function createPermissionDetails(array $userGroup, string $rightLevel): PermissionDetails
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $permissionDetails = new PermissionDetails();
        $permissionDetails->userGroupIdentifier = Type::getType(Type::INTEGER)
            ->convertToPhpValue($userGroup['user_group_identifier'], $platform);
        $permissionDetails->userGroupName = Type::getType(Type::STRING)
            ->convertToPhpValue($userGroup['user_group_name'], $platform);
        $permissionDetails->rightLevel = $rightLevel;

        return $permissionDetails;
    }
}
