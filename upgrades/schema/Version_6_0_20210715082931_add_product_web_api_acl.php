<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210715082931_add_product_web_api_acl extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function up(Schema $schema): void
    {
        $acls = [
            'pim_api_product_edit',
            'pim_api_product_list',
        ];
        $aclManager = $this->container->get('oro_security.acl.manager');
        $roles = $this->container->get('pim_user.repository.role')->findAll();
        foreach ($acls as $acl) {
            foreach ($roles as $role) {
                $privilege = new AclPrivilege();
                $identity = new AclPrivilegeIdentity(sprintf('action:%s', $acl));
                $privilege
                    ->setIdentity($identity)
                    ->addPermission(new AclPermission('EXECUTE', AccessLevel::SYSTEM_LEVEL));

                $aclManager->getPrivilegeRepository()
                    ->savePrivileges(new RoleSecurityIdentity($role), new ArrayCollection([$privilege]));
                $aclManager->flush();
            }
        }
        $aclManager->clearCache();
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
