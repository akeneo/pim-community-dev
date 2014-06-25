<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * Load fixtures for roles
 *
 * @author    nicolas dupont <nicolas@akeneo.com>
 * @copyright 2014 akeneo sas (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  open software license (osl 3.0)
 */
class LoadRoleData extends AbstractInstallerFixture
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om   = $manager;
        $aclManager = $this->getAclManager();
        $dataRoles  = Yaml::parse(realpath($this->getFilePath()));
        $roles      = [];

        $roleAnonymous = $this->buildRole(['role' => 'IS_AUTHENTICATED_ANONYMOUSLY', 'label' => 'Anonymous']);
        $manager->persist($roleAnonymous);

        foreach ($dataRoles['user_roles'] as $code => $dataRole) {
            $dataRole['role']= $code;
            $role = $this->buildRole($dataRole);
            $roles[]= $role;
            $manager->persist($role);
        }
        $manager->flush();

        foreach ($roles as $role) {
            $this->loadAcls($aclManager, $role);
        }
        $aclManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'user_roles';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 105;
    }

    /**
     * Build the role entity from data
     *
     * @param array $data
     *
     * @return Role
     */
    protected function buildRole(array $data)
    {
        $role = $data['role'];
        $label = $data['label'];
        $role = new Role($role);
        $role->setLabel($label);
        $owner = isset($data['owner']) ? $data['owner'] : 'Main';
        $owner = $this->getOwner($owner);
        $role->setOwner($owner);

        return $role;
    }

    /**
     * Load the ACL per role
     *
     * @param AclManager $manager
     * @param Role       $role
     *
     * @see Oro\Bundle\SecurityBundle\DataFixtures\ORM\LoadAclRoles
     */
    protected function loadAcls(AclManager $manager, Role $role)
    {
        $sid = $manager->getSid($role);

        foreach ($manager->getAllExtensions() as $extension) {
            $rootOid = $manager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $fullAccessMask = $maskBuilder->hasConst('GROUP_SYSTEM')
                    ? $maskBuilder->getConst('GROUP_SYSTEM')
                    : $maskBuilder->getConst('GROUP_ALL');
                $manager->setPermission($sid, $rootOid, $fullAccessMask, true);
            }
        }
    }

    /**
     * @return AclManager
     */
    protected function getAclManager()
    {
        return $this->container->get('oro_security.acl.manager');
    }

    /**
     * Get the owner (business unit) from code
     *
     * @param string $owner
     *
     * @return \Oro\Bundle\OrganizationBundle\Entity\BusinessUnit
     */
    protected function getOwner($owner)
    {
        return $this->om
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => $owner));
    }
}
