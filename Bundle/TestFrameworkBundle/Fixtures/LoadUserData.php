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

        $this->loadAttributes($userManager);

        $admin = $userManager->createUser();

        $role  = $manager
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));
        $group = $manager
            ->getRepository('OroUserBundle:Group')
            ->findOneBy(array('name' => 'Administrators'));

        $unit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => 'Main'));

        $api   = new UserApi();
        $api->setApiKey('admin_api_key')
            ->setUser($admin);

        $admin
            ->setUsername('admin')
            ->setPlainPassword('admin')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('admin@example.com')
            ->setApi($api)
            ->addRole($role)
            ->addGroup($group)
            ->setBusinessUnits(
                new ArrayCollection(array($unit))
            )
            ->setOwner($unit);

        $this->addReference('default_user', $admin);

        $userManager->updateUser($admin);
    }

    /**
     * Persist object
     *
     * @param UserManager $entityManager
     * @param mixed $object
     * @return void
     */
    private function persist($entityManager, $object)
    {
        $entityManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @param UserManager $entityManager
     * @return void
     */
    private function flush($entityManager)
    {
        $entityManager->getStorageManager()->flush();
    }

    public function getOrder()
    {
        return 110;
    }
}
