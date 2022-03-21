<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
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
        private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        private FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        private ScopeListComparatorInterface $scopeListComparator,
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
        $originalScopes = $this->getConnectedAppScopesQuery->execute($app->getId());
        $requestedScopes = $appAuthorization->getAuthorizationScopes()->getScopes();

        $newScopes = $this->scopeListComparator->diff(
            $requestedScopes,
            $originalScopes
        );

        $isFirstConnection = null === $this->findOneConnectedAppByIdQuery->execute($app->getId());

        $oldAuthorizationScopeMessages = $isFirstConnection ? null : $this->scopeMapperRegistry->getMessages($originalScopes);
        $newAuthorizationScopeMessages = $this->scopeMapperRegistry->getMessages($newScopes);

        $authenticationScopesThatRequireConsent = \array_filter(
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            fn (string $scope) => $scope !== AuthenticationScope::SCOPE_OPENID
        );

        return new JsonResponse([
            'appName' => $app->getName(),
            'appLogo' => $app->getLogo(),
            'appUrl' => $app->getUrl(),
            'oldScopeMessages' => $oldAuthorizationScopeMessages,
            'scopeMessages' => $newAuthorizationScopeMessages,
            'authenticationScopes' => \array_values($authenticationScopesThatRequireConsent)
        ]);
    }
}
