<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupPermission;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;

class SqlReferenceEntityPermissionRepository implements ReferenceEntityPermissionRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function save(ReferenceEntityPermission $referenceEntityPermission): void
    {
        $userPermissions = $referenceEntityPermission->getPermissions();
        $referenceEntityIdentifier = $referenceEntityPermission->getReferenceEntityIdentifier()->normalize();

        $normalizedPermissions = [];
        foreach ($userPermissions as $userPermission) {
            $normalizedPermission = $userPermission->normalize();
            $normalizedPermission['reference_entity_identifier'] = $referenceEntityIdentifier;

            $normalizedPermissions[] = $normalizedPermission;
        }

        $this->sqlConnection->transactional(function (Connection $connection) use ($normalizedPermissions, $referenceEntityIdentifier) {
            $deleteSql = <<<SQL
                DELETE FROM akeneo_reference_entity_reference_entity_permissions
                WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
            $connection->executeUpdate(
                $deleteSql,
                [
                    'reference_entity_identifier' => $referenceEntityIdentifier
                ]
            );

            foreach ($normalizedPermissions as $normalizedPermission) {
                $insertSql = <<<SQL
                    INSERT INTO akeneo_reference_entity_reference_entity_permissions
                        (reference_entity_identifier, user_group_identifier, right_level)
                    VALUES 
                        (:reference_entity_identifier, :user_group_identifier, :right_level);
SQL;
                $affectedRows = $this->sqlConnection->executeUpdate(
                    $insertSql,
                    $normalizedPermission
                );
                if ($affectedRows === 0) {
                    throw new \RuntimeException('Expected to create reference entity permissions, but none were inserted.');
                }
            }
        });
    }

    public function getByReferenceEntityIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): ReferenceEntityPermission {

        $fetch = <<<SQL
        SELECT reference_entity_identifier, user_group_identifier, right_level
        FROM akeneo_reference_entity_reference_entity_permissions
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['reference_entity_identifier' => (string) $referenceEntityIdentifier]
        );

        $userGroupPermissions = $this->hydrateUserGroupPermissions($statement);

        return ReferenceEntityPermission::create($referenceEntityIdentifier, $userGroupPermissions);
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
