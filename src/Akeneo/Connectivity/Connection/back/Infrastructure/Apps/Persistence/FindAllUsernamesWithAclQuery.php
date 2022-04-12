<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllUsernamesWithAclQuery implements FindAllUsernamesWithAclQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $acl): array
    {
        $selectSQL = <<<SQL
        SELECT oro_user.username
        FROM acl_entries
        JOIN acl_security_identities ON acl_security_identities.id = acl_entries.security_identity_id
        JOIN acl_classes ON acl_entries.class_id = acl_classes.id
        JOIN oro_access_role ON oro_access_role.role = acl_security_identities.identifier
        JOIN oro_user_access_role on oro_access_role.id = oro_user_access_role.role_id
        JOIN oro_user on oro_user_access_role.user_id = oro_user.id
        WHERE acl_classes.class_type = :acl
        AND acl_entries.mask = 1

        UNION DISTINCT

        SELECT oro_user.username
        FROM acl_entries
        JOIN acl_security_identities ON acl_security_identities.id = acl_entries.security_identity_id
        JOIN acl_classes ON acl_entries.class_id = acl_classes.id
        LEFT JOIN acl_object_identities on acl_object_identities.id = acl_entries.object_identity_id
        JOIN oro_access_role ON oro_access_role.role = acl_security_identities.identifier
        JOIN oro_user_access_role on oro_access_role.id = oro_user_access_role.role_id
        JOIN oro_user on oro_user_access_role.user_id = oro_user.id
        WHERE (acl_classes.class_type = "(root)" AND acl_object_identities.object_identifier = "action")
        OR acl_classes.class_type = :acl
        GROUP BY oro_user.username
        HAVING COUNT(acl_classes.class_type) = 1
        SQL;

        $results = $this->connection->executeQuery($selectSQL, ['acl' => $acl])->fetchFirstColumn();

        return $results ?: [];
    }
}
