<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\UserGroup;

use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\UserGroup\FindUserGroupsForSecurityIdentifierInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindUserGroupsForSecurityIdentifier implements FindUserGroupsForSecurityIdentifierInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @return UserGroupIdentifier[]
     */
    public function __invoke(SecurityIdentifier $securityIdentifier): array
    {
        $results = $this->fetchUserGroupIdentifiers($securityIdentifier);

        return $this->hydrateUserGroupIdentifiers($results);

    }

    private function fetchUserGroupIdentifiers(SecurityIdentifier $securityIdentifier): array
    {
        $query = <<<SQL
SELECT group_id 
FROM oro_user_access_group ug INNER JOIN oro_user u ON ug.user_id = u.id
WHERE u.username = :security_identifier
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['security_identifier' => $securityIdentifier->stringValue()]
        );
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    private function hydrateUserGroupIdentifiers($results): array
    {
        if (!$results) {
            return [];
        }

        $platform = $this->sqlConnection->getDatabasePlatform();
        $userGroupIdentifiers = array_map(function ($normalizedUserGroupIdentifier) use ($platform) {
            $identifier = Type::getType(Type::INTEGER)->convertToPhpValue(
                $normalizedUserGroupIdentifier['group_id'],
                $platform
            );

            return UserGroupIdentifier::fromInteger($identifier);
        }, $results);

        return $userGroupIdentifiers;
    }
}
