<?php

namespace Context\Loader;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Pim\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;

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
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['users'])) {
            foreach ($configuration['users'] as $username => $data) {
                $this->createUser($username, $data);
            }
        }

        $this->flush();
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

        $user = $this->userManager->createUser();

        $api = new UserApi();
        $api->setApiKey($apiKey)->setUser($user);

        $unit = $this
            ->userManager
            ->getStorageManager()
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => 'Main'));

        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setEmail($email)
            ->setApi($api)
            ->setOwner($unit)
            ->setBusinessUnits(
                new ArrayCollection(array($unit))
            );

        foreach ($roles as $role) {
            $user->addRole($this->getOrCreateRole($role));
        }

        $catalogLocale = $this->getLocaleManager()->getLocaleByCode($data['catalogLocale']);
        $user->setCatalogLocale($catalogLocale);

        $catalogScope = $this->getChannelManager()->getChannelByCode($data['catalogScope']);
        $user->setCatalogScope($catalogScope);

        $trees = $this->getCategoryManager()->getTrees();
        foreach ($trees as $tree) {
            if ($tree->getCode() === $data['defaultTree']) {
                $defaultTree = $tree;
                break;
            }
        }
        $user->setDefaultTree($defaultTree);

        $this->userManager->updateUser($user);
    }

    /**
     * @param string $code
     *
     * @return Role
     */
    protected function getOrCreateRole($code)
    {
        $role = $this
            ->userManager
            ->getStorageManager()
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => $code));

        if (!$role) {
            $role = new Role($code);
            $this->persist($role);
        }

        return $role;
    }
}
