<?php

namespace Pim\Bundle\UserBundle\Security;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindUserForSecurity;
use Pim\Component\User\User\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Implementation of Symfony UserProviderInterface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserProvider implements UserProviderInterface
{
    /** @var UserRepositoryInterface */
    protected $findUserForSecurityQuery;

    /**
     * @param FindUserForSecurity $findUserForSecurityQuery
     */
    public function __construct(FindUserForSecurity $findUserForSecurityQuery)
    {
        $this->findUserForSecurityQuery = $findUserForSecurityQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username): UserInterface
    {
        try {
            $user = ($this->findUserForSecurityQuery)($username);
        } catch (ResourceNotFoundException $exception) {
            throw new UsernameNotFoundException(
                sprintf('User with username "%s" does not exist.', $username),
                0,
                $exception
            );
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

        $reloadedUser = $this->loadUserByUsername($user->getUsername());

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return is_subclass_of($class, UserInterface::class);
    }
}
