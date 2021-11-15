<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
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
    private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator;
    private GetAppConfirmationQueryInterface $appConfirmationQuery;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        RequestAppAuthorizationHandler $handler,
        RouterInterface $router,
        FeatureFlag $featureFlag,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        GetAppConfirmationQueryInterface $appConfirmationQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->handler = $handler;
        $this->router = $router;
        $this->featureFlag = $featureFlag;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->appConfirmationQuery = $appConfirmationQuery;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if($request->query->get('scope', '') === 'openid'){
            //if app.partner = akeneo
            //Assume that the APP is already activated $appConfirmation->getAppId() must return smthg
            $appConfirmation = $this->appConfirmationQuery->execute($request->query->get('client_id', ''));

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
                    $request->query->get('client_id', ''),
                    $request->query->get('scope', ''),
                    $request->query->get('redirect_uri', ''),
                    $request->query->get('state', null)
                ),
                $userConfirmation
            );

            return new RedirectResponse($url);
        }

        $command = new RequestAppAuthorizationCommand(
            $request->query->get('client_id', ''),
            $request->query->get('response_type', ''),
            $request->query->get('scope', ''),
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

        return new RedirectResponse('/#' . $this->router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
            'client_id' => $command->getClientId(),
        ]));
    }
}
