<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
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
class AuthorizeAction
{
    private RequestAppAuthorizationHandler $requestAppAuthorizationHandler;
    private RouterInterface $router;
    private FeatureFlag $featureFlag;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator;
    private ConnectedPimUserProvider $connectedPimUserProvider;
    private RequestAppAuthenticationHandler $requestAppAuthenticationHandler;

    private SecurityFacade $security;

    public function __construct(
        RequestAppAuthorizationHandler $requestAppAuthorizationHandler,
        RouterInterface $router,
        FeatureFlag $featureFlag,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        ConnectedPimUserProvider $connectedPimUserProvider,
        RequestAppAuthenticationHandler $requestAppAuthenticationHandler,
        SecurityFacade $security,
    ) {
        $this->requestAppAuthorizationHandler = $requestAppAuthorizationHandler;
        $this->router = $router;
        $this->featureFlag = $featureFlag;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->connectedPimUserProvider = $connectedPimUserProvider;
        $this->requestAppAuthenticationHandler = $requestAppAuthenticationHandler;
        $this->security = $security;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (
            !$this->security->isGranted('akeneo_connectivity_connection_manage_apps')
            && !$this->security->isGranted('akeneo_connectivity_connection_open_apps')
        ) {
            throw new AccessDeniedHttpException();
        }

        $clientId = $request->query->get('client_id', '');

        $command = new RequestAppAuthorizationCommand(
            $clientId,
            $request->query->get('response_type', ''),
            $request->query->get('scope', ''),
            $request->query->get('state', null),
        );

        try {
            $this->requestAppAuthorizationHandler->handle($command);
        } catch (InvalidAppAuthorizationRequest $e) {
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
                    'new_authentication_scopes' => implode(',', $e->getNewAuthenticationScopes()),
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
}
