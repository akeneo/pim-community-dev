<?php

namespace Pim\Upgrade\Schema;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * Set to false all ACLs on the API for all roles
 */
class Version_1_7_20170116172612_acl extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $acls = [
            'pim_api_attribute_edit',
            'pim_api_attribute_list',
            'pim_api_attribute_option_edit',
            'pim_api_attribute_option_list',
            'pim_api_category_edit',
            'pim_api_category_list',
            'pim_api_family_edit',
            'pim_api_family_list',
            'pim_api_overall_access'
        ];

        $aclManager = $this->container->get('oro_security.acl.manager');
        $roles = $this->container->get('pim_user.repository.role')->findAll();

        foreach ($acls as $acl) {
            foreach ($roles as $role) {
                $privilege = new AclPrivilege();
                $identity = new AclPrivilegeIdentity(sprintf('action:%s', $acl));
                $privilege
                    ->setIdentity($identity)
                    ->addPermission(new AclPermission('EXECUTE', 0));

                $aclManager->getPrivilegeRepository()
                    ->savePrivileges(new RoleSecurityIdentity($role), new ArrayCollection([$privilege]));
            }
        }

        $aclManager->clearCache();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
