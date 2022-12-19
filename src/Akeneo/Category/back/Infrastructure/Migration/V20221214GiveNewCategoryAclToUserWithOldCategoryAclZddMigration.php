<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Migration;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigration implements ZddMigration
{
    private const ACL_CATEGORY_CREATE = 'pim_enrich_product_category_create';
    private const ACL_CATEGORY_EDIT = 'pim_enrich_product_category_edit';
    private const ACL_ENRICH_CATEGORY_TEMPLATE = 'pim_enrich_product_category_template';
    private const ACL_ENRICH_CATEGORY_EDIT_ATTRIBUTES = 'pim_enrich_product_category_edit_attributes';
    private const ACL_ENRICH_CATEGORY_ORDER_TREES = 'pim_enrich_product_category_order_trees';

    public function __construct(
        private AclManager $aclManager,
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
        private Connection $connection,
    )
    {
    }

    public function migrate(): void
    {
        $roles = $this->getRoles();

        foreach ($roles as $role) {
            $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($role);
            $grantedPermissions = $roleWithPermissions->permissions();

            // Roles with ACL to create category will also have the right to manage category template
            if (
                isset($grantedPermissions['action:' . self::ACL_CATEGORY_CREATE])
                && true === $grantedPermissions['action:' . self::ACL_CATEGORY_CREATE]
            ) {
                $grantedPermissions['action:' . self::ACL_ENRICH_CATEGORY_TEMPLATE] = true;
            }

            // Roles with ACL to edit category will also have the right to:
            // - manage category template
            // - edit category attributes
            // - order category trees
            if (
                isset($grantedPermissions['action:' . self::ACL_CATEGORY_EDIT])
                && true === $grantedPermissions['action:' . self::ACL_CATEGORY_EDIT]
            ) {
                $grantedPermissions['action:' . self::ACL_ENRICH_CATEGORY_TEMPLATE] = true;
                $grantedPermissions['action:' . self::ACL_ENRICH_CATEGORY_EDIT_ATTRIBUTES] = true;
                $grantedPermissions['action:' . self::ACL_ENRICH_CATEGORY_ORDER_TREES] = true;
            }

            $roleWithPermissions->setPermissions($grantedPermissions);
            $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

            $this->aclManager->flush();
            $this->aclManager->clearCache();
        }
    }

    public function getName(): string
    {
        return 'GiveToUsersNewEnrichedCategoriesAclsBasedOnLegacyCategoriesAcls';
    }

    /**
     * @return array<string>
     * @throws Exception
     */
    private function getRoles(): array
    {
        $data = $this->connection->fetchAllAssociative(<<<SQL
                SELECT identifier
                FROM acl_security_identities
        SQL);

        return $data;
    }
}
