<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * In the current version of the PIM (3.0.0), we can log in with either our username or email.
 * In SSO context we just want to check the `username` attribute.
 */
final class Provider implements UserProviderInterface
{
    private const CONFIGURATION_CODE = 'authentication_sso';

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var Repository */
    private $configRepository;

    public function __construct(UserRepositoryInterface $userRepository, Repository $configRepository)
    {
        $this->userRepository = $userRepository;
        $this->configRepository = $configRepository;
    }

    /**
     * TODO: Remove this function when symfony will be in 6.0
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!$this->isSSOEnabled()) {
            // If SSO is disabled, we want the next chained user provider to check if the user is valid.
            // The following exception is caught and silent.
            throw new UserNotFoundException('SSO feature is not enabled, let another UserProvider do its job.');
        }

        $user = $this->userRepository->findOneBy(['username' => $identifier]);
        if (null === $user) {
            throw new UserNotFoundException(sprintf('User with username "%s" does not exist.', $identifier));
        }

        if (!$user->isEnabled()) {
            throw new UserNotFoundException('User account is disabled.');
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
        if (null === $reloadedUser) {
            throw new UserNotFoundException(sprintf('User with id %d not found', $user->getId()));
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

    private function isSSOEnabled(): bool
    {
        try {
            $config = $this->configRepository->find(self::CONFIGURATION_CODE);
        } catch (ConfigurationNotFound $e) {
            return false;
        }

        return $config->isEnabled();
    }
}
