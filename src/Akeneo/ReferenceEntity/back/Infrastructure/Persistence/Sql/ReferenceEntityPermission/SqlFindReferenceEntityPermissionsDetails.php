<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\FindReferenceEntityPermissionsDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\PermissionDetails;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindReferenceEntityPermissionsDetails implements FindReferenceEntityPermissionsDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @return PermissionDetails[]
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $permissionDetails = $this->fetch($referenceEntityIdentifier);

        return $this->hydratePermissionDetails($permissionDetails);
    }

    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $query = <<<SQL
SELECT ug.id as user_group_identifier, ug.name as user_group_name, rp.right_level
FROM oro_access_group ug INNER JOIN akeneo_reference_entity_reference_entity_permissions rp ON ug.id = rp.user_group_identifier
WHERE rp.reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['reference_entity_identifier' => (string) $referenceEntityIdentifier]
        );
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return false !== $result ? $result : [];
    }

    /**
     * @return PermissionDetails[]
     */
    private function hydratePermissionDetails(array $permissionDetails): array
    {
        $platform = $this->sqlConnection->getDatabasePlatform();

        return array_map(
            function (array $normalizedPermissionDetails) use ($platform) {
                $permissionDetails = new PermissionDetails();
                $permissionDetails->userGroupIdentifier = Type::getType(Type::INTEGER)
                    ->convertToPhpValue($normalizedPermissionDetails['user_group_identifier'], $platform);
                $permissionDetails->userGroupName = Type::getType(Type::STRING)
                    ->convertToPhpValue($normalizedPermissionDetails['user_group_name'], $platform);
                $permissionDetails->rightLevel = Type::getType(Type::STRING)
                    ->convertToPhpValue($normalizedPermissionDetails['right_level'], $platform);

                return $permissionDetails;
            },
            $permissionDetails
        );
    }
}
