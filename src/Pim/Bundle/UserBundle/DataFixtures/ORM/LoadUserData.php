<?php

namespace Pim\Bundle\UserBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * Load required user data for PIM
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container   = $container;
        $this->userManager = $container->get('oro_user.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = $this->userManager->getRepository()->findAll();

        $localeCodes = $this->getLocaleManager()->getActiveCodes();
        $localeCode  = in_array('en_US', $localeCodes) ? 'en_US' : current($localeCodes);
        $locale      = $this->getLocaleManager()->getLocaleByCode($localeCode);
        $scope       = current($this->getChannelManager()->getChannels());
        $tree        = current($this->getCategoryManager()->getTrees());

        foreach ($users as $user) {
            $user->setCatalogLocale($locale);
            $user->setCatalogScope($scope);
            $user->setDefaultTree($tree);
            $this->persist($user);
        }

        $this->flush();
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_catalog.manager.locale');
    }

    /**
     * Get channel manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->container->get('pim_catalog.manager.channel');
    }

    /**
     * Get category manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }

    /**
     * Persist object
     *
     * @param mixed $object
     */
    protected function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     */
    protected function flush()
    {
        $this->userManager->getStorageManager()->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 112;
    }
}
