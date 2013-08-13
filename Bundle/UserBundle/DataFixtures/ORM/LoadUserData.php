<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\UserApi;

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
        /** @var \Oro\Bundle\UserBundle\Entity\UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $admin = $userManager->createUser();
        $api = new UserApi();

        $api->setApiKey('admin_api_key')
            ->setUser($admin);

        $admin
            ->setUsername('admin')
            ->setPlainPassword('admin')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->addRole($this->getReference('admin_role'))
            ->addGroup($this->getReference('oro_group_administrators'))
            ->setEmail('admin@example.com')
            ->setApi($api)
            ->setOwner($this->getReference('default_business_unit'));
        $this->addReference('default_user', $admin);
        $userManager->updateUser($admin);
    }

    public function getOrder()
    {
        return 110;
    }
}
