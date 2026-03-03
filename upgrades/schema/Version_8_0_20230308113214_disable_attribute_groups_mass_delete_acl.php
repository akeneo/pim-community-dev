<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_8_0_20230308113214_disable_attribute_groups_mass_delete_acl extends AbstractMigration implements ContainerAwareInterface
{
    private const ACL = 'pim_enrich_attributegroup_mass_delete';

    private ContainerInterface $container;

    public function up(Schema $schema): void
    {
        $aclManager = $this->container->get('oro_security.acl.manager');
        $roleWithPermissionsRepository = $this->container->get('pim_user.repository.role_with_permissions');
        $roleWithPermissionsSaver = $this->container->get('pim_user.saver.role_with_permissions');

        $rolesWithPermissions = [];

        foreach ($this->getRoles() as $role) {
            $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($role);
            $grantedPermissions = $roleWithPermissions->permissions();

            $permissionKey = sprintf('action:%s', self::ACL);
            $grantedPermissions[$permissionKey] = false;

            $roleWithPermissions->setPermissions($grantedPermissions);
            $rolesWithPermissions[] = $roleWithPermissions;
        }

        $roleWithPermissionsSaver->saveAll($rolesWithPermissions);
        $aclManager->flush();
        $aclManager->clearCache();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string[]
     */
    private function getRoles(): array
    {
        return $this->container->get('database_connection')->fetchFirstColumn(
            'SELECT role FROM oro_access_role',
        );
    }
}
