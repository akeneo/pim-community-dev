<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandler
{
    public function __construct(
        private ScopeMapperRegistryInterface $scopeMapperRegistry,
        private SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        private AuthorizationRequestNotifierInterface $authorizationRequestNotifier,
        private ScopeListComparatorInterface $scopeListComparator,
    ) {
    }

    public function handle(FlagAppContainingOutdatedScopesCommand $command): void
    {
        $connectedApp = $command->getConnectedApp();

        $existingScopes = ScopeList::fromScopes($connectedApp->getScopes());
        $supportedScopes = ScopeList::fromScopes($this->scopeMapperRegistry->getAllScopes());
        $requestedScopes = ScopeList::fromScopeString($command->getRequestedScopes());

        $allowedRequestedAuthorizationScopes = ScopeList::fromScopes(
            \array_intersect(
                $requestedScopes->getScopes(),
                $supportedScopes->getScopes()
            )
        );

        $newScopes = $this->scopeListComparator->diff(
            $allowedRequestedAuthorizationScopes->getScopes(),
            $existingScopes->getScopes()
        );

        if ($newScopes !== []) {
            $this->saveConnectedAppOutdatedScopesFlagQuery->execute($connectedApp->getId(), true);
            $this->authorizationRequestNotifier->notify($connectedApp);
        }
    }
}
