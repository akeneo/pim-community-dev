<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;
use Doctrine\DBAL\Connection;

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
            $affectedRows = $connection->executeUpdate(
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
            }
        });
    }
}
