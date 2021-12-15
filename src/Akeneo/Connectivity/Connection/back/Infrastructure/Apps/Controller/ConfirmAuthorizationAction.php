<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationAction
{
    private CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler;
    private FeatureFlag $featureFlag;
    private GetAppConfirmationQueryInterface $getAppConfirmationQuery;
    private ViolationListNormalizer $violationListNormalizer;
    private SecurityFacade $security;
    private LoggerInterface $logger;
    private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private ConnectedPimUserProvider $connectedPimUserProvider;
    private ConsentAppAuthenticationHandler $consentAppAuthenticationHandler;

    public function __construct(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        SecurityFacade $security,
        LoggerInterface $logger,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler
    ) {
        $this->createAppWithAuthorizationHandler = $createAppWithAuthorizationHandler;
        $this->featureFlag = $featureFlag;
        $this->getAppConfirmationQuery = $getAppConfirmationQuery;
        $this->violationListNormalizer = $violationListNormalizer;
        $this->security = $security;
        $this->logger = $logger;
        $this->redirectUriWithAuthorizationCodeGenerator = $redirectUriWithAuthorizationCodeGenerator;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->connectedPimUserProvider = $connectedPimUserProvider;
        $this->consentAppAuthenticationHandler = $consentAppAuthenticationHandler;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedPimUserId = $this->connectedPimUserProvider->getCurrentUserId();

        try {
            $this->createAppWithAuthorizationHandler->handle(new CreateAppWithAuthorizationCommand($clientId));

            $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
            if (null === $appAuthorization) {
                throw new \LogicException('There is no active app authorization in session');
            }

            if ($appAuthorization->getAuthenticationScopes()->hasScope(AuthenticationScope::SCOPE_OPENID)) {
                $this->consentAppAuthenticationHandler->handle(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId));
            }
        } catch (InvalidAppAuthorizationRequest $exception) {
            $this->logger->warning(
                sprintf('App activation failed with validation error "%s"', $exception->getMessage())
            );

            return new JsonResponse([
                'errors' => $this->violationListNormalizer->normalize($exception->getConstraintViolationList()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $appConfirmation = $this->getAppConfirmationQuery->execute($clientId);
        if (null === $appConfirmation) {
            throw new \LogicException('The connected app should have been created');
        }

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $connectedPimUserId
        );

        return new JsonResponse([
            'appId' => $appConfirmation->getAppId(),
            'userGroup' => $appConfirmation->getUserGroup(),
            'redirectUrl' => $redirectUrl,
        ]);
    }
}
