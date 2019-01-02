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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * This query finds the Reference Entity identifiers for which the given user group is the last one
 * to have the edit permission on.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(int $userGroupId): array
    {
        $sql = "SELECT perm1.reference_entity_identifier, COUNT(*) as nb
                FROM akeneo_reference_entity_reference_entity_permissions perm1
                INNER JOIN akeneo_reference_entity_reference_entity_permissions perm2
                 ON perm1.reference_entity_identifier = perm2.reference_entity_identifier
                 AND perm1.right_level = perm2.right_level
                 AND perm1.right_level = 'edit'
                 AND perm1.user_group_identifier = :userGroupIdentifier
                GROUP BY perm1.reference_entity_identifier
                HAVING nb = 1;
        ";

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['userGroupIdentifier' => $userGroupId],
            ['userGroupId' => PDO::PARAM_INT]
        );

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $referenceEntityIdentifiers = array_map(function (array $result) {
            return $result['reference_entity_identifier'];
        }, $results);

        return $referenceEntityIdentifiers;
    }
}
