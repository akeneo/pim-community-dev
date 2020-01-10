<?php

namespace Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Implementation of Symfony UserProviderInterface
 *
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserApiProvider implements UserProviderInterface
{
    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findOneByIdentifier($username);
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $userClass = ClassUtils::getClass($user);
        if (!$this->supportsClass($userClass)) {
            throw new UnsupportedUserException(sprintf('User object of class "%s" is not supported.', $userClass));
        }

        $reloadedUser = $this->userRepository->find($user->getId());
        if (null === $reloadedUser) {
            throw new UsernameNotFoundException(sprintf('User with id %d not found', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Akeneo\UserManagement\Component\Model\UserInterface');
    }
}
