<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load fixtures for users
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadUserData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;

        $filePath = realpath($this->getFilePath());
        $dataUsers = Yaml::parse(realpath($this->getFilePath()));

        foreach ($dataUsers['users'] as $dataUser) {
            $user = $this->buildUser($dataUser);
            $this->getUserManager()->updateUser($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * Get user manager
     *
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('oro_user.manager');
    }

    /**
     * Create a user
     *
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    protected function createUser()
    {
        return $this->getUserManager()->createUser();
    }

    /**
     * Build the user entity from data
     *
     * @param array $data
     *
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    protected function buildUser(array $data)
    {
        $user = $this->createUser();

        $owner = $this->getOwner($data['owner']);
        $role  = $this->getRole($data['role']);

        $user
            ->setUsername($data['username'])
            ->setPlainPassword($data['password'])
            ->setEmail($data['email'])
            ->setFirstName($data['firstname'])
            ->setLastName($data['lastname'])
            ->setEnabled($data['enable'])
            ->addRole($role)
            ->setOwner($owner)
            ->addBusinessUnit($owner);

        $locale = $this->getLocale($data['catalog_locale']);
        $user->setCatalogLocale($locale);

        $channel = $this->getChannel($data['catalog_scope']);
        $user->setCatalogScope($channel);

        $tree = $this->getTree($data['default_tree']);
        $user->setDefaultTree($tree);

        return $user;
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

    /**
     * Get the role from code
     *
     * @param string $role
     *
     * @return \Oro\Bundle\UserBundle\Entity\Role
     */
    protected function getRole($role)
    {
        return $this->om
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => $role));
    }

    /**
     * Get the locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_catalog.manager.locale');
    }

    /**
     * Get locale entity from locale code
     *
     * @param string $localeCode
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\Locale
     */
    protected function getLocale($localeCode)
    {
        return $this->getLocaleManager()->getLocaleByCode($localeCode);
    }

    /**
     * Get the channel manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->container->get('pim_catalog.manager.channel');
    }

    /**
     * Get channel entity from channel code
     *
     * @param string $channelCode
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\Channel
     */
    protected function getChannel($channelCode)
    {
        return $this->getChannelManager()->getChannelByCode($channelCode);
    }

    /**
     * Get the category manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }

    /**
     * Get tree entity from category code
     *
     * @param string $treeCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Category
     */
    protected function getTree($tree)
    {
        return $this->getCategoryManager()->getEntityRepository()->findOneBy(array('code' => $tree));
    }
}
