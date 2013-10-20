<?php

namespace Oro\Bundle\CalendarBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class UpdateAclRoles extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
            $this->updateUserRole($manager);
            $manager->flush();
        }
    }

    protected function updateUserRole(AclManager $manager)
    {
        // deny to view other user's calendar
        // @todo: seems that data fixtures should be loader after EntityConfig initialization
        // error: An ACL extension was not found for: entity:Oro\Bundle\CalendarBundle\Entity\CalendarConnection
        // $sid = $manager->getSid($this->getReference('user_role'));
        // $oid = $manager->getOid('entity:Oro\Bundle\CalendarBundle\Entity\CalendarConnection');
        // $maskBuilder = $manager->getMaskBuilder($oid);
        // $manager->setPermission($sid, $oid, $maskBuilder->get());
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 26;
    }
}
