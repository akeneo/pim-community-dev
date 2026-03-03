<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210908142400_add_product_web_api_acl extends AbstractMigration implements ContainerAwareInterface
{
    private const ACLS = [
        'pim_api_product_list' => true,
        'pim_api_product_edit' => true,
        'pim_api_product_remove' => true,
    ];

    private ?ContainerInterface $container;

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        $roles = $this->getRoles();

        foreach (self::ACLS as $acl => $granted) {
            if (!$this->aclIsRegistered($acl)) {
                $this->registerAcl($acl);
            }

            foreach ($roles as $role) {
                if ($this->aclIsAlreadyDefinedForRole($role, $acl)) {
                    continue;
                }

                if ($granted && $this->roleIsGrantedEverythingByDefault($role)) {
                    continue;
                }

                $this->addAclToRole($role, $acl, $granted);
            }
        }

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');
        $aclManager->clearCache();
    }

    private function aclIsRegistered(string $acl): bool
    {
        return (bool) $this->connection->fetchOne(
            <<<SQL
SELECT COUNT(*)
FROM acl_classes
WHERE class_type = :acl
SQL,
            [
                'acl' => $acl,
            ]
        );
    }

    private function registerAcl(string $acl): void
    {
        $this->connection->executeQuery(
            <<<SQL
INSERT INTO acl_classes (class_type)
VALUES (:acl)
SQL,
            [
                'acl' => $acl,
            ]
        );

        $aclClassId = (int) $this->connection->fetchOne(
            <<<SQL
SELECT id
FROM acl_classes
WHERE class_type = :acl
SQL,
            [
                'acl' => $acl,
            ]
        );

        $this->connection->executeQuery(
            <<<SQL
INSERT INTO acl_object_identities (
    parent_object_identity_id,
    class_id,
    object_identifier,
    entries_inheriting
)
VALUES (
    null,
    :acl_class_id,
    'action',
    1
)
SQL,
            [
                'acl_class_id' => $aclClassId,
            ]
        );

        $aclObjectIdentityId = (int) $this->connection->fetchOne(
            <<<SQL
SELECT id
FROM acl_object_identities
WHERE class_id = :acl_class_id
SQL,
            [
                'acl_class_id' => $aclClassId,
            ]
        );

        $this->connection->executeQuery(
            <<<SQL
INSERT INTO acl_object_identity_ancestors (
    object_identity_id,
    ancestor_id
)
VALUES (
    :object_identity_id,
    :object_identity_id
)
SQL,
            [
                'object_identity_id' => $aclObjectIdentityId,
            ]
        );
    }

    private function aclIsAlreadyDefinedForRole(string $role, string $acl): bool
    {
        return (bool) $this->connection->fetchOne(
            <<<SQL
SELECT COUNT(*)
FROM acl_entries
JOIN acl_security_identities ON acl_security_identities.id = acl_entries.security_identity_id
JOIN acl_classes ON acl_entries.class_id = acl_classes.id
WHERE acl_security_identities.identifier = :role
AND acl_classes.class_type = :acl
SQL,
            [
                'acl' => $acl,
                'role' => $role,
            ]
        );
    }

    private function addAclToRole(string $role, string $acl, bool $granted): void
    {
        $classId = (int) $this->connection->fetchOne(
            <<<SQL
SELECT id
FROM acl_classes
WHERE class_type = :acl
SQL,
            [
                'acl' => $acl,
            ]
        );

        $securityEntityId = (int) $this->connection->fetchOne(
            <<<SQL
SELECT id
FROM acl_security_identities
WHERE identifier = :role
SQL,
            [
                'role' => $role,
            ]
        );

        // ace_order is numeroted from 0 to n, across all the roles for a given acl_class.
        // the observed behavior is that the last added entry for the acl_class will have the ace_order "0"
        // and all the previous ones increase by one.
        // I'm not sure why.
        // Also, the ace_order is also discrimated through the 2 trees of permissions "entities" & "action",
        // and each tree is ordered separatly. That's why I'm restricting the query to only the ones related
        // to the "action" tree.
        // Anyway, I'm increasing all the existing ones by one and in the follow-up query, with the insert into,
        // I will insert "0".
        $this->connection->executeQuery(
            <<<SQL
UPDATE acl_entries
SET ace_order = ace_order + 1
WHERE acl_entries.class_id = :class_id
AND (
    acl_entries.object_identity_id IS NULL
    OR
    acl_entries.object_identity_id = (
        SELECT aoi.id
        FROM acl_object_identities aoi
        JOIN acl_classes ac on aoi.class_id = ac.id
        WHERE object_identifier = "action"
        AND ac.class_type = "(root)"
        LIMIT 1
    )
)
ORDER BY ace_order DESC
SQL,
            [
                'class_id' => $classId,
            ]
        );

        $this->connection->executeQuery(
            <<<SQL
INSERT INTO acl_entries (
    class_id,
    object_identity_id,
    security_identity_id,
    field_name,
    ace_order,
    mask,
    granting,
    granting_strategy,
    audit_success,
    audit_failure
)
VALUES (
    :class_id,
    null,
    :security_identity_id,
    null,
    0,
    1,
    :granting,
    'all',
    0,
    0
)
SQL,
            [
                'class_id' => $classId,
                'security_identity_id' => $securityEntityId,
                'granting' => $granted,
            ]
        );
    }

    private function roleIsGrantedEverythingByDefault(string $role): bool
    {
        return (bool) $this->connection->fetchOne(
            <<<SQL
SELECT COUNT(*)
FROM acl_entries
JOIN acl_object_identities ON acl_entries.object_identity_id = acl_object_identities.id
JOIN acl_security_identities ON acl_security_identities.id = acl_entries.security_identity_id
JOIN acl_classes on acl_object_identities.class_id = acl_classes.id
WHERE acl_security_identities.identifier = :role
AND acl_classes.class_type = "(root)"
AND acl_object_identities.object_identifier = "action"
AND acl_entries.mask = 1
SQL,
            [
                'role' => $role,
            ]
        );
    }

    /**
     * @return string[]
     */
    private function getRoles(): array
    {
        return array_map(function($row) {
            return $row['identifier'];
        }, $this->connection->fetchAllAssociative(
            <<<SQL
SELECT identifier
FROM acl_security_identities
SQL
        ));
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }
}
