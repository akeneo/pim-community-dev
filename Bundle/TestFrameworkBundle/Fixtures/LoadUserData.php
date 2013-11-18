<?php
namespace Oro\Bundle\TestFrameworkBundle\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $admin  = $manager
            ->getRepository('OroUserBundle:User')
            ->findOneBy(array('username' => 'admin'));

        $role  = $manager
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));
        $group = $manager
            ->getRepository('OroUserBundle:Group')
            ->findOneBy(array('name' => 'Administrators'));

        $unit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => 'Main'));

        if (!$admin) {
            $admin = $userManager->createUser();
            $admin
                ->setUsername('admin')
                ->addRole($role)
                ->addGroup($group);
        }

        $admin->setPlainPassword('admin')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('admin@example.com')
            ->setOwner($unit)
            ->setBusinessUnits(
                new ArrayCollection(array($unit))
            );

        $api   = new UserApi();
        if (!$admin->getApi()) {
            $api->setApiKey('admin_api_key')
                ->setUser($admin);
            $admin->setApi($api);
        }

        $this->addReference('default_user', $admin);

        $userManager->updateUser($admin);
    }

    public function getOrder()
    {
        return 110;
    }
}
