<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * Unauthorize by default the permission to edit catalog rules
 */
final class Version_5_0_20200623100751_add_record_created_updated_at extends AbstractMigration implements ContainerAwareInterface
{
    private const ACL_ID = 'pimee_catalog_rule_rule_edit_permissions';

    /** @var ContainerInterface */
    private $container;

    public function up(Schema $schema) : void
    {
        $aclManager = $this->container->get('oro_security.acl.manager');
        $roles = $this->container->get('pim_user.repository.role')->findAll();

        foreach ($roles as $role) {
            $privilege = new AclPrivilege();
            $identity = new AclPrivilegeIdentity(sprintf('action:%s', static::ACL_ID));
            $privilege
                ->setIdentity($identity)
                ->addPermission(new AclPermission('EXECUTE', 0));

            $aclManager->getPrivilegeRepository()
                ->savePrivileges(new RoleSecurityIdentity($role), new ArrayCollection([$privilege]));
        }

        $aclManager->flush();
        $aclManager->clearCache();
    }

    public function down(Schema $schema) : void
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
}
