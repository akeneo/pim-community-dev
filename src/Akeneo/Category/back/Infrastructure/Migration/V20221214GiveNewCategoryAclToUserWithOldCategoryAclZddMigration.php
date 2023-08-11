<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Migration;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Psr\Log\LoggerInterface;

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

    private string $currentRoleName = '';
    private array $grantedPermissions = [];

    public function __construct(
        private AclManager $aclManager,
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $roles = $this->getRoles();

        foreach ($roles as $role) {
            $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($role);
            if ($roleWithPermissions) {
                $this->grantedPermissions = $roleWithPermissions->permissions();
                $this->currentRoleName = $role;

                // Roles with ACL to create category will also have the right to manage category template
                if (
                    isset($this->grantedPermissions['action:'.self::ACL_CATEGORY_CREATE])
                    && true === $this->grantedPermissions['action:'.self::ACL_CATEGORY_CREATE]
                ) {
                    $this->grantPermission('action:'.self::ACL_ENRICH_CATEGORY_TEMPLATE);
                }

                // Roles with ACL to edit category will also have the right to:
                // - manage category template
                // - edit category attributes
                // - order category trees
                if (
                    isset($this->grantedPermissions['action:'.self::ACL_CATEGORY_EDIT])
                    && true === $this->grantedPermissions['action:'.self::ACL_CATEGORY_EDIT]
                ) {
                    $this->grantPermission('action:'.self::ACL_ENRICH_CATEGORY_TEMPLATE);
                    $this->grantPermission('action:'.self::ACL_ENRICH_CATEGORY_EDIT_ATTRIBUTES);
                    $this->grantPermission('action:'.self::ACL_ENRICH_CATEGORY_ORDER_TREES);
                }

                $roleWithPermissions->setPermissions($this->grantedPermissions);
                $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

                $this->aclManager->flush();
                $this->aclManager->clearCache();

                $this->grantedPermissions = [];
                $this->currentRoleName = '';
            }
        }
    }

    public function migrateNotZdd(): void
    {
        // Do nothing
    }

    public function getName(): string
    {
        return 'GiveToUsersNewEnrichedCategoriesAclsBasedOnLegacyCategoriesAcls';
    }

    /**
     * @return array<string>
     *
     * @throws Exception
     */
    private function getRoles(): array
    {
        return $this->connection->fetchFirstColumn(<<<SQL
            SELECT identifier
            FROM acl_security_identities
        SQL);

        return $data;
    }

    private function grantPermission(string $aclName): void
    {
        if (!isset($this->grantedPermissions[$aclName]) || $this->grantedPermissions[$aclName] === false) {
            $this->grantedPermissions[$aclName] = true;
            $this->logger->notice(sprintf('pim:zdd-migration:migrate - Add \'%s\' acl to role \'%s\'', $aclName, $this->currentRoleName));
        }
    }
}
