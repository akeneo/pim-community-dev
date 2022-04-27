<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
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
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $clientId = $request->query->get('client_id', '');

        if ('' === $clientId || null === $app = $this->getAppQuery->execute($clientId)) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                ])
            );
        }

        $this->denyAccessUnlessGrantedToManageOrOpen($app);

        $command = new RequestAppAuthorizationCommand(
            $clientId,
            $request->query->get('response_type', ''),
            $request->query->get('scope', ''),
            $app->getCallbackUrl(),
            $request->query->get('state', null),
        );

        try {
            $this->requestAppAuthorizationHandler->handle($command);
        } catch (InvalidAppAuthorizationRequestException $e) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => $e->getConstraintViolationList()[0]->getMessage(),
                ])
            );
        }

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        // Check if the App is authorized
        $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if (null === $appConfirmation) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'client_id' => $command->getClientId(),
                ])
            );
        }

        $connectedPimUserId = $this->connectedPimUserProvider->getCurrentUserId();

        try {
            $this->requestAppAuthenticationHandler->handle(new RequestAppAuthenticationCommand(
                $appConfirmation->getAppId(),
                $connectedPimUserId,
                $appAuthorization->getAuthenticationScopes(),
            ));
        } catch (UserConsentRequiredException $e) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authenticate', [
                    'client_id' => $e->getAppId(),
                    'new_authentication_scopes' => \implode(',', $e->getNewAuthenticationScopes()),
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

    private function denyAccessUnlessGrantedToManageOrOpen(App $app): void
    {
        if (
            !$app->isTestApp() &&
            !$this->security->isGranted('akeneo_connectivity_connection_manage_apps') &&
            !$this->security->isGranted('akeneo_connectivity_connection_open_apps')
        ) {
            throw new AccessDeniedHttpException();
        }

        if (
            $app->isTestApp() &&
            !$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')
        ) {
            throw new AccessDeniedHttpException();
        }
    }
}
