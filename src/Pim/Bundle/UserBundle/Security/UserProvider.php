<?php

namespace Pim\Bundle\UserBundle\Security;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Pim\Bundle\UserBundle\Persistence\ORM\Query;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Loads UserInterface objects from some source for the authentication system.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserProvider implements UserProviderInterface
{
    /** @var Query\FindAuthenticatedUser */
    private $findAuthenticatedUserQuery;

    /**
     * @param Query\FindAuthenticatedUser $findAuthenticatedUserQuery
     */
    public function __construct(Query\FindAuthenticatedUser $findAuthenticatedUserQuery)
    {
        $this->findAuthenticatedUserQuery = $findAuthenticatedUserQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username): UserInterface
    {
        try {
            $user = ($this->findAuthenticatedUserQuery)($username);
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
        $reloadedUser = $this->loadUserByUsername($user->getUsername());

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return $class instanceof UserInterface;
    }
}
