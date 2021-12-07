<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\AppAuthenticationUserProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\OpenIdScopeMapper;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizeAction
{
    private RequestAppAuthorizationHandler $handler;
    private RouterInterface $router;
    private FeatureFlag $featureFlag;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator;
    private AppAuthenticationUserProvider $appAuthenticationUserProvider;
    private ConnectedPimUserProvider $connectedPimUserProvider;

    public function __construct(
        RequestAppAuthorizationHandler $handler,
        RouterInterface $router,
        FeatureFlag $featureFlag,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthenticationUserProvider $appAuthenticationUserProvider,
        ConnectedPimUserProvider $connectedPimUserProvider,
    ) {
        $this->handler = $handler;
        $this->router = $router;
        $this->featureFlag = $featureFlag;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->appAuthenticationUserProvider = $appAuthenticationUserProvider;
        $this->connectedPimUserProvider = $connectedPimUserProvider;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $clientId = $request->query->get('client_id', '');
        $scope = $request->query->get('scope', '');

        $command = new RequestAppAuthorizationCommand(
            $clientId,
            $request->query->get('response_type', ''),
            $scope,
            $request->query->get('state', null),
        );

        try {
            $this->handler->handle($command);
        } catch (InvalidAppAuthorizationRequest $e) {
            return new RedirectResponse(
                '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => $e->getConstraintViolationList()[0]->getMessage(),
                ])
            );
        }

        // Check if the App is already authorized
        $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if (null !== $appConfirmation) {
            $appAuthenticationUser = $this->appAuthenticationUserProvider->getAppAuthenticationUser(
                $appConfirmation->getAppId(),
                $this->connectedPimUserProvider->getCurrentUserId()
            );

            $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
            if (null === $appAuthorization) {
                throw new \LogicException('There is no active app authorization in session');
            }

            if (
                $appAuthorization->getAuthenticationScopes()->hasScope(OpenIdScopeMapper::SCOPE_OPENID)
                && false === $appAuthenticationUser->getConsentedAuthenticationScopes()->equals($appAuthorization->getAuthenticationScopes())
            ) {
                // @TODO might loop if it decline the app => redirect on pim, with error ?
                // @TODO triggers the consent step 'akeneo_connectivity_connection_connect_apps_authenticate'
                echo 'Display consent modal!';
                die;
            }

            $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
                $appAuthorization,
                $appConfirmation,
                $appAuthenticationUser
            );

            return new RedirectResponse($redirectUrl);
        }

        return new RedirectResponse(
            '/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                'client_id' => $command->getClientId(),
            ])
        );
    }
}
