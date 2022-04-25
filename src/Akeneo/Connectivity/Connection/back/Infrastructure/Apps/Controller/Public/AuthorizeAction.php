<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthorizeAction
{
    public function __construct(
        private RequestAppAuthorizationHandler $requestAppAuthorizationHandler,
        private RouterInterface $router,
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        private ConnectedPimUserProvider $connectedPimUserProvider,
        private RequestAppAuthenticationHandler $requestAppAuthenticationHandler,
        private SecurityFacade $security,
        private GetAppQueryInterface $getAppQuery,
        private GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        private ScopeListComparatorInterface $scopeListComparator,
        private UpdateConnectedAppScopesWithAuthorizationHandler $updateConnectedAppScopesWithAuthorizationHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $clientId = $request->query->get('client_id', '');
        $responseType = $request->query->get('response_type', '');
        $requestedRawScopes = $request->query->get('scope', '');
        $state = $request->query->get('state');

        if ('' === $clientId || null === $app = $this->getAppQuery->execute($clientId)) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                ])
            );
        }

        $this->denyAccessUnlessGrantedToManageOrOpen($app);

        $isFirstAuthorizationRequest = null === $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if ($isFirstAuthorizationRequest) {
            $this->denyAccessUnlessGrantedToManage($app);
        }

        try {
            $appAuthorization = $this->saveAuthorizationRequestInSession(
                $clientId,
                $responseType,
                $requestedRawScopes,
                $app->getCallbackUrl(),
                $state,
            );
        } catch (InvalidAppAuthorizationRequestException $e) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => $e->getConstraintViolationList()[0]->getMessage(),
                ])
            );
        }

        $hasNewScopes = $this->hasNewScopes($clientId, $appAuthorization);

        if (($isFirstAuthorizationRequest || $hasNewScopes) && $this->isGrantedToManage($app)) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'client_id' => $clientId,
                ])
            );
        }

        if ($this->isGrantedToManage($app)) {
            $this->updateConnectedAppScopesWithAuthorizationHandler->handle(new UpdateConnectedAppScopesWithAuthorizationCommand($clientId));
        }

        $connectedPimUserId = $this->connectedPimUserProvider->getCurrentUserId();

        try {
            $this->requestAppAuthenticationHandler->handle(new RequestAppAuthenticationCommand(
                $appConfirmation->getAppId(),
                $connectedPimUserId,
                $appAuthorization->getAuthenticationScopes(),
            ));
        } catch (UserConsentRequiredException) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authenticate', [
                    'client_id' => $clientId,
                ])
            );
        }

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $connectedPimUserId
        );

        return new RedirectResponse($redirectUrl);
    }

    private function isGrantedToManage(App $app): bool
    {
        return
            (
                $app->isTestApp() &&
                $this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')
            ) || (
                !$app->isTestApp() &&
                $this->security->isGranted('akeneo_connectivity_connection_manage_apps')
            );
    }

    private function isGrantedToOpen(App $app): bool
    {
        return
            (
                $app->isTestApp() &&
                $this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')
            ) || (
                !$app->isTestApp() &&
                $this->security->isGranted('akeneo_connectivity_connection_open_apps')
            );
    }

    private function denyAccessUnlessGrantedToManage(App $app): void
    {
        if (!$this->isGrantedToManage($app)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function denyAccessUnlessGrantedToManageOrOpen(App $app): void
    {
        if (!$this->isGrantedToManage($app) && !$this->isGrantedToOpen($app)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @throws InvalidAppAuthorizationRequestException
     */
    private function saveAuthorizationRequestInSession(
        string $clientId,
        string $responseType,
        string $scope,
        string $appCallbackUrl,
        ?string $state,
    ): AppAuthorization {
        $this->requestAppAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            $clientId,
            $responseType,
            $scope,
            $appCallbackUrl,
            $state,
        ));

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        return $appAuthorization;
    }

    private function hasNewScopes(string $clientId, AppAuthorization $appAuthorization): bool
    {
        $originalScopes = $this->getConnectedAppScopesQuery->execute($clientId);
        $requestedScopes = $appAuthorization->getAuthorizationScopes()->getScopes();

        return false === empty($this->scopeListComparator->diff(
            $requestedScopes,
            $originalScopes
        ));
    }
}
