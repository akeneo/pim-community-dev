<?php

namespace Context\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData;
use Pim\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\UserBundle\Entity\UserApi;
use Symfony\Component\Yaml\Yaml;

/**
 * Loader for users
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserLoader extends LoadUserData
{
    /**
     * @var string Path of the fixtures file
     */
    protected $filePath;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;

        $configuration = Yaml::parse(file_get_contents(realpath($this->getFilePath())));

        if (isset($configuration['users'])) {
            foreach ($configuration['users'] as $username => $data) {
                $this->createUser($username, $data);
            }
        }

        $manager->flush();
    }

    /**
     * @param string $filePath
     *
     * @return UserLoader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $username
     * @param array  $data
     */
    protected function createUser($username, array $data)
    {
        $password  = isset($data['password']) ? $data['password'] : $username;
        $firstName = isset($data['first_name']) ? $data['first_name'] : ucfirst($username);
        $lastName  = isset($data['last_name']) ? $data['last_name'] : 'Doe';
        $email     = isset($data['email']) ? $data['email'] : $username . '@example.com';
        $apiKey    = isset($data['api_key']) ? $data['api_key'] : $username . '_api_key';
        $roles     = isset($data['roles']) ? $data['roles'] : array('ROLE_ADMINISTRATOR');
        $groups    = isset($data['groups']) ? $data['groups'] : array('all');

        $className = $this->container->getParameter('pim_user.entity.user.class');
        $user = new $className();

        $api = new UserApi();
        $api->setApiKey($apiKey)->setUser($user);

        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email)
            ->setApi($api);

        foreach ($roles as $role) {
            $user->addRole($this->getOrCreateRole($role));
        }

        foreach ($groups as $group) {
            $user->addGroup($this->getOrCreateGroup($group));
        }

        $user->setCatalogLocale($this->getLocale($data['catalogLocale']));
        $user->setUiLocale($this->getLocale($data['uiLocale']));
        $user->setCatalogScope($this->getChannel($data['catalogScope']));
        $user->setDefaultTree($this->getTree($data['defaultTree']));

        $this->container->get('pim_user.saver.user')->save($user);
    }

    /**
     * @param string $code
     *
     * @return Role
     */
    protected function getOrCreateRole($code)
    {
        $role = $this->getRole($code);

        if (!$role) {
            $role = new Role($code);
            // TODO use a Saver
            $this->om->persist($role);
            $this->om->flush();
        }

        return $role;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    protected function getOrCreateGroup($name)
    {
        $group = $this->getGroup($name);

        if (!$group) {
            $group = new Group($name);
            $this->om->persist($group);
            $this->om->flush();
        }

        return $group;
    }
}
