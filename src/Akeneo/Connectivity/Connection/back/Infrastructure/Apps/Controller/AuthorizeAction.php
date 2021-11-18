<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        RequestAppAuthorizationHandler $handler,
        RouterInterface $router,
        FeatureFlag $featureFlag,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->handler = $handler;
        $this->router = $router;
        $this->featureFlag = $featureFlag;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $clientId = $request->query->get('client_id', '');
        $scope = $request->query->get('scope', '');


        $scopes = explode(' ', $scope);

        if(in_array('openid', $scopes))
        {
            //if app.partner = akeneo
            //Assume that the APP is already activated $appConfirmation->getAppId() must return smthg
            $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);

            if(null === $appConfirmation)
            {
                return new Response('<h1 style="color:red">APP must be activated first, we need an access token for the APP before trying to connect to it.<br/> Go to you PIM/Connect/Apps/Yell-app/Connect.</h1>');
            }

            $userConfirmation = AppConfirmation::create(
                $appConfirmation->getAppId(),
                $this->tokenStorage->getToken()->getUser()->getId(),
                $appConfirmation->getUserGroup(),
                $appConfirmation->getFosClientId()
            );

            $url = $this->redirectUriWithAuthorizationCodeGenerator->generate(
                AppAuthorization::createFromRequest(
                    $clientId,
                    $scope,
                    $request->query->get('redirect_uri', ''),
                    $request->query->get('state', null)
                ),
                $userConfirmation
            );

            return new RedirectResponse($url);
        }

        $command = new RequestAppAuthorizationCommand(
            $clientId,
            $request->query->get('response_type', ''),
            $scope,
            $request->query->get('redirect_uri', ''),
            $request->query->get('state', null),
        );

        try {
            $this->handler->handle($command);
        } catch (InvalidAppAuthorizationRequest $e) {
            return new RedirectResponse('/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                'error' => $e->getConstraintViolationList()[0]->getMessage(),
            ]));
        }

        // Check if the App is already authorized
        $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if (null !== $appConfirmation) {
            return $this->createAuthorizedResponse($appConfirmation);
        }

        return new RedirectResponse('/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
            'client_id' => $command->getClientId(),
        ]));
    }

    private function createAuthorizedResponse(AppConfirmation $appConfirmation): Response
    {
        $clientId = $appConfirmation->getAppId();

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate($appAuthorization, $appConfirmation);

        return new RedirectResponse($redirectUrl);
    }
}
