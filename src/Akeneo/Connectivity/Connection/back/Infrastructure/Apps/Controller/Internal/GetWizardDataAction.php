<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\HasUserConsentForAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetWizardDataAction
{
    public function __construct(
        private GetAppQueryInterface $getAppQuery,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private ScopeMapperRegistry $scopeMapperRegistry,
        private FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        private ScopeListComparatorInterface $scopeListComparator,
        private ConnectedPimUserProvider $connectedPimUserProvider,
        private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        private HasUserConsentForAppQueryInterface $hasUserConsentForAppQuery,
    ) {
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $app = $this->getAppQuery->execute($clientId);

        if (null === $app) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        [$oldAuthorizationScopeMessages, $newAuthorizationScopeMessages, $isFirstConnection] = $this->getAuthorizationScopes($app->getId(), $appAuthorization);
        [$oldAuthenticationScopes, $newAuthenticationScopes] = $this->getAuthenticationScopes($app->getId(), $appAuthorization);

        return new JsonResponse([
            'appName' => $app->getName(),
            'appLogo' => $app->getLogo(),
            'appUrl' => $app->getUrl(),
            'appIsCertified' => $app->isCertified(),
            'oldScopeMessages' => $oldAuthorizationScopeMessages,
            'scopeMessages' => $newAuthorizationScopeMessages,
            'oldAuthenticationScopes' => $oldAuthenticationScopes,
            'authenticationScopes' => $newAuthenticationScopes,
            'displayCheckboxConsent' => $isFirstConnection,
        ]);
    }

    private function getAuthorizationScopes(string $appId, AppAuthorization $appAuthorization): array
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($appId);
        $isFirstConnection = null === $connectedApp;

        $originalScopes = $isFirstConnection ? null : $connectedApp->getScopes();
        $requestedScopes = $appAuthorization->getAuthorizationScopes()->getScopes();

        $newScopes = $this->scopeListComparator->diff(
            $requestedScopes,
            $originalScopes ?? []
        );

        $oldAuthorizationScopeMessages = $isFirstConnection ? null : $this->scopeMapperRegistry->getMessages($originalScopes);
        $newAuthorizationScopeMessages = $this->scopeMapperRegistry->getMessages($newScopes);

        return [$oldAuthorizationScopeMessages, $newAuthorizationScopeMessages, $isFirstConnection];
    }

    private function getAuthenticationScopes(string $appId, AppAuthorization $appAuthorization): array
    {
        $userId = $this->connectedPimUserProvider->getCurrentUserId();
        $isFirstUserConnection = !$this->hasUserConsentForAppQuery->execute($userId, $appId);

        $oldAuthenticationScopes = $isFirstUserConnection ? null : $this->filterAuthenticationScopesThatRequireConsent(
            $this->getUserConsentedAuthenticationScopesQuery->execute($userId, $appId)
        );

        $newAuthenticationScopes = $this->filterAuthenticationScopesThatRequireConsent(
            $appAuthorization->getAuthenticationScopes()->getScopes()
        );
        if (!$isFirstUserConnection) {
            $newAuthenticationScopes = \array_unique(\array_diff($newAuthenticationScopes, $oldAuthenticationScopes ?? []));
        }
        \sort($newAuthenticationScopes);

        return [$oldAuthenticationScopes, $newAuthenticationScopes];
    }

    /**
     * @param array<string> $scopes
     *
     * @return array<string>
     */
    private function filterAuthenticationScopesThatRequireConsent(array $scopes): array
    {
        return \array_values(\array_filter(
            $scopes,
            fn (string $scope): bool => $scope !== AuthenticationScope::SCOPE_OPENID
        ));
    }
}
