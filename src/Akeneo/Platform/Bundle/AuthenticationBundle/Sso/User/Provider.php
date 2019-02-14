<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
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
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!$this->isSSOEnabled()) {
            // If SSO is disabled, we want the next chained user provider to check if the user is valid.
            // The following exception is catched and silent.
            throw new UsernameNotFoundException('SSO feature is not enabled, let another UserProvider do its job.');
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (null === $user) {
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

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Symfony\Component\Security\Core\User\UserInterface');
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
