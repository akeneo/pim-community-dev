<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthenticationUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthenticationUser;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthenticationUserProvider implements AppAuthenticationUserProviderInterface
{
    private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        UserRepositoryInterface $userRepository
    ) {
        $this->getUserConsentedAuthenticationScopesQuery = $getUserConsentedAuthenticationScopesQuery;
        $this->userRepository = $userRepository;
    }

    public function getAppAuthenticationUser(string $appId, int $pimUserId): AppAuthenticationUser
    {
        $user = $this->getUser($pimUserId);
        $consentedAuthenticationScopes = ScopeList::fromScopes($this->getUserConsentedAuthenticationScopesQuery->execute($pimUserId, $appId));

        return new AppAuthenticationUser(
            $pimUserId,
            $consentedAuthenticationScopes,
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName()
        );
    }

    private function getUser(int $id): UserInterface
    {
        $user = $this->userRepository->find($id);
        if (false === $user instanceof UserInterface) {
            throw new \RuntimeException();
        }

        return $user;
    }
}
