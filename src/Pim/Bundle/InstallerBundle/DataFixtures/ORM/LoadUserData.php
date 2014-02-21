<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData extends AbstractInstallerFixture
{
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

    public function getEntity()
    {
        return 'users';
    }

    public function getOrder()
    {
        return 30;
    }

    protected function getUserManager()
    {
        return $this->container->get('oro_user.manager');
    }

    protected function createUser()
    {
        return $this->getUserManager()->createUser();
    }

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

    protected function getOwner($owner)
    {
        return $this->om
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => $owner));
    }

    protected function getRole($role)
    {
        return $this->om
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => $role));
    }

    protected function getLocaleManager()
    {
        return $this->container->get('pim_catalog.manager.locale');
    }

    protected function getLocale($locale)
    {
        return $this->getLocaleManager()->getLocaleByCode($locale);
    }

    protected function getChannelManager()
    {
        return $this->container->get('pim_catalog.manager.channel');
    }

    protected function getChannel($channel)
    {
        return $this->getChannelManager()->getChannelByCode($channel);
    }

    protected function getCategoryManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }

    protected function getTree($tree)
    {
        return $this->getCategoryManager()->getEntityRepository()->findOneBy(array('code' => $tree));
    }
}
