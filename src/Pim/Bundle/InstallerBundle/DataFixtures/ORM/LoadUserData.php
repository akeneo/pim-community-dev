<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @var ObjectManager
     */
    protected $om;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;

        $dataUsers = Yaml::parse(file_get_contents(realpath($this->getFilePath())));

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
        return 110;
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
     * Build the user entity from data
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return UserInterface
     */
    protected function buildUser(array $data)
    {
        $user = $this->getUserManager()->createUser();
        $user
            ->setUsername($data['username'])
            ->setPlainPassword($data['password'])
            ->setEmail($data['email'])
            ->setFirstName($data['firstname'])
            ->setLastName($data['lastname'])
            ->setEnabled($data['enable']);

        if (!isset($data['roles'])) {
            throw new \Exception(sprintf('user %s must have defined roles', $data['username']));
        }
        foreach ($data['roles'] as $code) {
            $role = $this->getRole($code);
            $user->addRole($role);
        }

        if (!isset($data['groups'])) {
            throw new \Exception(sprintf('user %s must have defined groups', $data['username']));
        }
        foreach ($data['groups'] as $code) {
            $group = $this->getGroup($code);
            $user->addGroup($group);
        }

        $user->addGroup($this->getGroup('all'));

        $locale = $this->getLocale($data['catalog_locale']);
        $user->setCatalogLocale($locale);

        $channel = $this->getChannel($data['catalog_scope']);
        $user->setCatalogScope($channel);

        $tree = $this->getTree($data['default_tree']);
        $user->setDefaultTree($tree);

        return $user;
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
     * Get the group from code
     *
     * @param string $group
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group
     */
    protected function getGroup($group)
    {
        return $this->om
            ->getRepository('OroUserBundle:Group')
            ->findOneBy(array('name' => $group));
    }

    /**
     * Get locale entity from locale code
     *
     * @param string $localeCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function getLocale($localeCode)
    {
        $localeManager = $this->container->get('pim_catalog.manager.locale');
        $locale        = $localeManager->getLocaleByCode($localeCode);

        return $locale ? $locale : current($localeManager->getActiveLocales());
    }

    /**
     * Get channel entity from channel code
     *
     * @param string $channelCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannel($channelCode)
    {
        $channelManager = $this->container->get('pim_catalog.manager.channel');
        $channel        = $channelManager->getChannelByCode($channelCode);

        return $channel ? $channel : current($channelManager->getChannels());
    }

    /**
     * Get tree entity from category code
     *
     * @param string $categoryCode
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Category
     */
    protected function getTree($categoryCode)
    {
        $categoryRepository = $this->container->get('pim_catalog.repository.category');
        $category           = $categoryRepository->findOneBy(['code' => $categoryCode, 'parent' => null]);

        return $category ? $category : current($categoryRepository->getTrees());
    }
}
