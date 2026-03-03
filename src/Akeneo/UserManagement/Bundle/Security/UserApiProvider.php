<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
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
     * @TODO: Remove this function when symfony will be in 6.0
     */
    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): SecurityUserInterface
    {
        $user = $this->userRepository->findOneByIdentifier($identifier);
        if (!$user || false === $user->isApiUser()) {
            throw new UserNotFoundException(sprintf('User with username "%s" does not exist or is not a Api user.', $identifier));
        }

        if (!$user->isEnabled()) {
            throw new DisabledException('User account is disabled.');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $userClass = ClassUtils::getClass($user);
        if (!$this->supportsClass($userClass)) {
            throw new UnsupportedUserException(sprintf('User object of class "%s" is not supported.', $userClass));
        }

        $reloadedUser = $this->userRepository->find($user->getId());
        if (null === $reloadedUser || false === $reloadedUser->isApiUser()) {
            throw new UserNotFoundException(sprintf('User with id %d does not exist or is not a Api user.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return is_subclass_of($class, 'Akeneo\UserManagement\Component\Model\UserInterface');
    }
}
