<?php

namespace Oro\Bundle\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class LoadAclRoles extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load ACL for security roles
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var AclManager $manager */
        $manager = $this->container->get('oro_security.acl.manager');

        if ($manager->isAclEnabled()) {
            $this->loadSuperAdminRole($manager);
            $this->loadAdminRole($manager);
            $this->loadUserRole($manager);
            $manager->flush();
        }
    }

    protected function loadSuperAdminRole(AclManager $manager)
    {
        $sid = $manager->getSid($this->getReference('admin_role'));

        foreach ($manager->getAllExtensions() as $extension) {
            $rootOid = $manager->getRootOid($extension->getRootId());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $fullAccessMask = $maskBuilder->hasConst('GROUP_SYSTEM')
                    ? $maskBuilder->getConst('GROUP_SYSTEM')
                    : $maskBuilder->getConst('GROUP_ALL');
                $manager->setPermission($sid, $rootOid, $fullAccessMask, true);
            }
        }
    }

    protected function loadAdminRole(AclManager $manager)
    {
        $sid = $manager->getSid($this->getReference('administrator_role'));

        foreach ($manager->getAllExtensions() as $extension) {
            $rootOid = $manager->getRootOid($extension->getRootId());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                if ($maskBuilder->hasConst('GROUP_GLOBAL')) {
                    if ($maskBuilder->hasConst('MASK_VIEW_SYSTEM')) {
                        $mask =
                            $maskBuilder->getConst('MASK_VIEW_SYSTEM')
                            | $maskBuilder->getConst('MASK_CREATE_GLOBAL')
                            | $maskBuilder->getConst('MASK_EDIT_GLOBAL')
                            | $maskBuilder->getConst('MASK_DELETE_GLOBAL')
                            | $maskBuilder->getConst('MASK_ASSIGN_GLOBAL')
                            | $maskBuilder->getConst('MASK_SHARE_GLOBAL');
                    } else {
                        $mask = $maskBuilder->getConst('GROUP_GLOBAL');
                    }
                } else {
                    $mask = $maskBuilder->getConst('GROUP_ALL');
                }
                $manager->setPermission($sid, $rootOid, $mask, true);
            }
        }
    }

    protected function loadUserRole(AclManager $manager)
    {
        $sid = $manager->getSid($this->getReference('user_role'));

        foreach ($manager->getAllExtensions() as $extension) {
            $rootOid = $manager->getRootOid($extension->getRootId());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                if ($maskBuilder->hasConst('GROUP_BASIC')) {
                    if ($maskBuilder->hasConst('MASK_VIEW_SYSTEM')) {
                        $mask =
                            $maskBuilder->getConst('MASK_VIEW_SYSTEM')
                            | $maskBuilder->getConst('MASK_CREATE_BASIC')
                            | $maskBuilder->getConst('MASK_EDIT_BASIC')
                            | $maskBuilder->getConst('MASK_DELETE_BASIC')
                            | $maskBuilder->getConst('MASK_ASSIGN_BASIC')
                            | $maskBuilder->getConst('MASK_SHARE_BASIC');
                    } else {
                        $mask = $maskBuilder->getConst('GROUP_BASIC');
                    }
                } else {
                    $mask = $maskBuilder->getConst('GROUP_NONE');
                }
                $manager->setPermission($sid, $rootOid, $mask, true);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 25;
    }
}
