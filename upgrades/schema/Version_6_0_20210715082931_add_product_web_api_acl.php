<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210715082931_add_product_web_api_acl extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->container->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->container->get('pim_user.saver.role_with_permissions');
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->container->get('pim_user.repository.role');
        /** @var UnitOfWorkAndRepositoriesClearer $cacheClearer */
        $cacheClearer = $this->container->get('pim_connector.doctrine.cache_clearer');

        /** @var Role[] $roles */
        $roles = $roleRepository->findAll();

        foreach ($roles as $role) {
            $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($role->getRole());
            if (null === $roleWithPermissions) {
                continue;
            }

            $permissions = $roleWithPermissions->permissions();
            $permissions['action:pim_api_product_list'] = true;
            $permissions['action:pim_api_product_edit'] = true;
            $permissions['action:pim_api_product_remove'] = true;
            $roleWithPermissions->setPermissions($permissions);

            $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);
        }

        $aclManager->flush();

        $aclManager->clearCache();
        $cacheClearer->clear();
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
