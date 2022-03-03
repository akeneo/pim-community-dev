<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
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
    private GetAppQueryInterface $getAppQuery;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private ScopeMapperRegistry $scopeMapperRegistry;

    public function __construct(
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ScopeMapperRegistry $scopeMapperRegistry
    ) {
        $this->getAppQuery = $getAppQuery;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->scopeMapperRegistry = $scopeMapperRegistry;
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

        $authorizationScopeMessages = $this->scopeMapperRegistry->getMessages($appAuthorization->getAuthorizationScopes()->getScopes());

        $authenticationScopesThatRequireConsent = \array_filter(
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            fn (string $scope) => $scope !== AuthenticationScope::SCOPE_OPENID
        );

        return new JsonResponse([
            'appName' => $app->getName(),
            'appLogo' => $app->getLogo(),
            'appUrl' => $app->getUrl(),
            'scopeMessages' => $authorizationScopeMessages,
            'authenticationScopes' => \array_values($authenticationScopesThatRequireConsent)
        ]);
    }
}
