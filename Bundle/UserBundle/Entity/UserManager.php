<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class UserManager implements UserProviderInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $flexManager;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * Constructor
     *
     * @param string                  $class          Entity name
     * @param ObjectManager           $om             Object manager
     * @param FlexibleManager         $flexManager    Proxied flexible manager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct($class, ObjectManager $om, $flexManager, EncoderFactoryInterface $encoderFactory)
    {
        $metadata = $om->getClassMetadata($class);

        $this->class          = $metadata->getName();
        $this->om             = $om;
        $this->flexManager    = $flexManager;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Returns an empty user instance
     *
     * @return User
     */
    public function createUser()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * Updates a user
     *
     * @param  User              $user
     * @param  bool              $flush Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateUser(User $user, $flush = true)
    {
        $this->updatePassword($user);

        // we need to make sure to have at least one role
        if ($user->getRolesCollection()->isEmpty()) {
            $role = $this->getStorageManager()
                ->getRepository('OroUserBundle:Role')->findOneBy(array('role' => User::ROLE_DEFAULT));

            if (!$role) {
                throw new \RuntimeException('Default user role not found');
            }

            $user->addRole($role);
        }

        $this->getStorageManager()->persist($user);

        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Updates a user password if a plain password is set
     *
     * @param User $user
     */
    public function updatePassword(User $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->getEncoder($user);

            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }
    }

    /**
     * Deletes a user
     *
     * @param User $user
     */
    public function deleteUser(User $user)
    {
        $this->getStorageManager()->remove($user);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one user by the given criteria
     *
     * @param  array $criteria
     * @return User
     */
    public function findUserBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Returns a collection with all user instances
     *
     * @return \Traversable
     */
    public function findUsers()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Finds a user by email
     *
     * @param  string $email
     * @return User
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $email));
    }

    /**
     * Finds a user by username
     *
     * @param  string $username
     * @return User
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array('username' => $username));
    }

    /**
     * Finds a user either by email, or username
     *
     * @param  string $usernameOrEmail
     * @return User
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Finds a user either by confirmation token
     *
     * @param  string $token
     * @return User
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }

    /**
     * Reloads a user
     *
     * @param User $user
     */
    public function reloadUser(User $user)
    {
        $this->getStorageManager()->refresh($user);
    }

    /**
     * Refreshed a user by User Instance
     *
     * It is strongly discouraged to use this method manually as it bypasses
     * all ACL checks.
     *
     * @param  SecurityUserInterface    $user
     * @return User
     * @throws UnsupportedUserException if a User Instance is given which is not managed by this UserManager
     *                                  (so another Manager could try managing it)
     * @throws UsernameNotFoundException if user could not be reloaded
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        $class = $this->getClass();

        if (!$user instanceof $class) {
            throw new UnsupportedUserException('Account is not supported');
        }

        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Expected an instance of Oro\Bundle\UserBundle\Entity\User, but got "%s"', get_class($user))
            );
        }

        $refreshedUser = $this->findUserBy(array('id' => $user->getId()));

        if (null === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * Loads a user by username.
     * It is strongly discouraged to call this method manually as it bypasses
     * all ACL checks.
     *
     * @param  string                    $username
     * @return User
     * @throws UsernameNotFoundException if user not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass();
    }

    protected function getEncoder(User $user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * Returns basic query instance to get collection with all user instances
     *
     * @return QueryBuilder
     */
    public function getListQuery()
    {
        return $this->getStorageManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('OroUserBundle:User', 'u')
            ->orderBy('u.id', 'ASC');
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->getStorageManager()->getRepository($this->getClass());
    }

    /**
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->om;
    }

    public function __call($name, $args)
    {
        if (method_exists($this->flexManager, $name)) {
            return call_user_func_array(array($this->flexManager, $name), $args);
        }

        throw new \RuntimeException(sprintf('Unknown method "%s"', $name));
    }
}
