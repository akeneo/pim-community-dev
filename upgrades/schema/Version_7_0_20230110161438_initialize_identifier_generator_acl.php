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
final class Version_7_0_20230110161438_initialize_identifier_generator_acl extends AbstractMigration implements ContainerAwareInterface
{
    private const ACL_IDENTIFIER_GENERATOR_VIEW = 'pim_identifier_generator_view';
    private const ACL_IDENTIFIER_GENERATOR_MANAGE = 'pim_identifier_generator_manage';
    private const ACL_RULE_ENGINE_VIEW = 'pimee_catalog_rule_rule_view_permissions';
    private const ACL_RULE_ENGINE_EDIT = 'pimee_catalog_rule_rule_edit_permissions';

    /** @var ContainerInterface */
    private $container;

    public function getDescription(): string
    {
        return 'Sets the `pim_identifier_generator_manage` and `pim_identifier_generator_view` ACLs according to the rule engine permissions';
    }

    public function up(Schema $schema): void
    {
        $aclManager = $this->container->get('oro_security.acl.manager');
        $roleWithPermissionsRepository = $this->container->get('pim_user.repository.role_with_permissions');
        $roleWithPermissionsSaver = $this->container->get('pim_user.saver.role_with_permissions');

        $rolesWithPermissions = [];

        foreach ($this->getRoles() as $role) {
            $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($role);
            $grantedPermissions = $roleWithPermissions->permissions();

            if ($grantedPermissions['action:' . self::ACL_RULE_ENGINE_EDIT] ?? false) {
                // if the edit rules permission is granted, then every identifier generator permission is granted
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_MANAGE)] = true;
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_VIEW)] = true;
            } elseif ($grantedPermissions['action:' . self::ACL_RULE_ENGINE_VIEW] ?? true) {
                // if the view rules permission is granted or does not exist (CE/GE),then only the view identifier genartor permission is granted
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_MANAGE)] = false;
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_VIEW)] = true;
            } else {
                // if the rule permissions exist but are not granted, then no identifier generator permissionis granted
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_MANAGE)] = false;
                $grantedPermissions[sprintf('action:%s', self::ACL_IDENTIFIER_GENERATOR_VIEW)] = false;
            }

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
    private function getRoles(): array {
        return $this->container->get('database_connection')->fetchFirstColumn(
            'SELECT role FROM oro_access_role'
        );
    }
}
