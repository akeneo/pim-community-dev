<?php
namespace Pim\Bundle\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\UserBundle\Entity\User;

/**
 * Load user
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadUser extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // TODO: we should use https://github.com/FriendsOfSymfony/FOSUserBundle/blob/1.2.0/Resources/doc/user_manager.md
        // not depends on implemention

        $user = new User();
        $user->setUsername('John');
        $user->setUsernameCanonical('John');
        $user->setEmail('john.doe@example.com');
        $user->setEmailCanonical('john.doe@example.com');
        $user->setEnabled(1);
//        $user->setSalt('salt');
        $user->setPassword('pwd');
        $user->setLocked(0);
        $user->setExpired(0);
        $user->setRoles(array('ADMIN'));
        $user->setCredentialsExpired(0);
        $this->manager->persist($user);

        $this->manager->flush();
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }

}