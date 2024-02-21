<?php

namespace Akeneo\UserManagement\Bundle\Manager;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserManager implements UserProviderInterface
{
    public function __construct(
        protected string $class,
        protected ObjectManager $om,
        protected PasswordHasherFactoryInterface $encoderFactory,
        private SaverInterface $saver
    ) {
    }

    /**
     * Updates a user
     *
     * @param  SecurityUserInterface $user
     * @throws \RuntimeException
     */
    public function updateUser(SecurityUserInterface $user)
    {
        $this->updatePassword($user);

        // we need to make sure to have at least one role
        if ($user->getRolesCollection()->isEmpty()) {
            $role = $this->getStorageManager()
                ->getRepository(Role::class)->findOneBy(['role' => User::ROLE_DEFAULT]);

            if (!$role) {
                throw new \RuntimeException('Default user role not found');
            }

            $user->addRole($role);
        }

        $this->saver->save($user);
    }

    /**
     * Updates a user password if a plain password is set
     *
     * @param UserInterface $user
     */
    public function updatePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->hash($password, $user->getSalt()));
        }
    }

    /**
     * Deletes a user
     *
     * @param SecurityUserInterface $user
     */
    public function deleteUser(SecurityUserInterface $user)
    {
        $this->getStorageManager()->remove($user);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one user by the given criteria
     *
     * @param  array $criteria
     * @return SecurityUserInterface
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
     * @return SecurityUserInterface
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(['email' => $email]);
    }

    /**
     * Finds a user by username
     *
     * @param  string $username
     * @return SecurityUserInterface
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(['username' => $username]);
    }

    /**
     * Finds a user either by email, or username
     *
     * @param  string $usernameOrEmail
     * @return SecurityUserInterface
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
     * @return SecurityUserInterface
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(['confirmationToken' => $token]);
    }

    /**
     * Reloads a user
     *
     * @param SecurityUserInterface $user
     */
    public function reloadUser(SecurityUserInterface $user)
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
     * @throws UnsupportedUserException if a User Instance is given which is not managed by this UserManager
     *                                  (so another Manager could try managing it)
     * @throws UserNotFoundException if user could not be reloaded
     * @return SecurityUserInterface
     */
    public function refreshUser(SecurityUserInterface $user): SecurityUserInterface
    {
        $class = $this->getClass();

        if (!$user instanceof $class) {
            throw new UnsupportedUserException('Account is not supported');
        }

        if (!$user instanceof SecurityUserInterface) {
            throw new UnsupportedUserException(
                sprintf('Expected an instance of Akeneo\UserManagement\Component\Model\UserInterface, but got "%s"', get_class($user))
            );
        }

        $refreshedUser = $this->findUserBy(['id' => $user->getId()]);

        if (null === $refreshedUser) {
            throw new UserNotFoundException(sprintf('User with ID "%d" could not be reloaded', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * @TODO: Remove this function when symfony will be in 6.0
     */
    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Loads a user by username.
     * It is strongly discouraged to call this method manually as it bypasses
     * all ACL checks.
     *
     * @throws UserNotFoundException if user not found
     */
    public function loadUserByIdentifier(string $identifier): SecurityUserInterface
    {
        $user = $this->findUserByUsername($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('No user with name "%s" was found.', $identifier));
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
    public function supportsClass($class): bool
    {
        return $class === $this->getClass();
    }

    protected function getEncoder(UserInterface $user)
    {
        return $this->encoderFactory->getPasswordHasher($user);
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
            ->from(UserInterface::class, 'u')
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
}
