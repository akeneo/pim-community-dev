<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\AppAuthenticationUserProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthenticationAction
{
    private FeatureFlag $featureFlag;
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private SecurityFacade $security;
    private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private AppAuthenticationUserProvider $appAuthenticationUserProvider;
    private ConnectedPimUserProvider $connectedPimUserProvider;
    private ConsentAppAuthenticationHandler $consentAppAuthenticationHandler;

    public function __construct(
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        SecurityFacade $security,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        AppAuthenticationUserProvider $appAuthenticationUserProvider,
        ConnectedPimUserProvider $connectedPimUserProvider,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler
    ) {
        $this->featureFlag = $featureFlag;
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->security = $security;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->appAuthenticationUserProvider = $appAuthenticationUserProvider;
        $this->connectedPimUserProvider = $connectedPimUserProvider;
        $this->consentAppAuthenticationHandler = $consentAppAuthenticationHandler;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        // @TODO is this acl mandatory for just accessing the app?
        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        // if (!$request->isXmlHttpRequest()) {
        //     return new RedirectResponse('/');
        // }

        // @TODO handle validation error
        $this->consentAppAuthenticationHandler->handle(new ConsentAppAuthenticationCommand($clientId));

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new \LogicException('There is no active app authorization in session');
        }

        $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if (null === $appConfirmation) {
            throw new \LogicException('The connected app should have been created');
        }

        $appAuthenticationUser = $this->appAuthenticationUserProvider->getAppAuthenticationUser(
            $appConfirmation->getAppId(),
            $this->connectedPimUserProvider->getCurrentUserId()
        );

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $appAuthenticationUser
        );

        return new RedirectResponse($redirectUrl);
        // return new JsonResponse([
        //     'appId' => $appConfirmation->getAppId(),
        //     'redirectUrl' => $redirectUrl,
        // ]);
    }
}
