<?php

namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadRolesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roleAnonymous = new Role('IS_AUTHENTICATED_ANONYMOUSLY');
        $roleAnonymous->setLabel('Anonymous');
        $this->addReference('anon_role', $roleAnonymous);

        $roleUser = new Role('ROLE_USER');
        $roleUser->setLabel('User');
        $this->addReference('user_role', $roleUser);

        $roleSAdmin = new Role('ROLE_SUPER_ADMIN');
        $roleSAdmin->setLabel('Super admin');
        $this->addReference('admin_role', $roleSAdmin);

        $roleAdmin = new Role('ROLE_ADMINISTRATOR');
        $roleAdmin->setLabel('Administrator');
        $this->addReference('administrator_role', $roleAdmin);

        $roleManager = new Role('ROLE_MANAGER');
        $roleManager->setLabel('Manager');
        $this->addReference('manager_role', $roleManager);

        $manager->persist($roleAnonymous);
        $manager->persist($roleUser);
        $manager->persist($roleAdmin);
        $manager->persist($roleSAdmin);
        $manager->persist($roleManager);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
