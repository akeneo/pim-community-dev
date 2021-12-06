<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ScopeFilterInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\AppAuthenticationUserProvider;
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
    public function __construct(
        private CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        private FeatureFlag $featureFlag,
        private GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        private ViolationListNormalizer $violationListNormalizer,
        private SecurityFacade $security,
        private LoggerInterface $logger,
        private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private AppAuthenticationUserProvider $appAuthenticationUserProvider,
        private CreateUserConsentQueryInterface $createUserConsentQuery,
        private Clock $clock,
        private ScopeFilterInterface $scopeFilter,
        private ConnectedPimUserProvider $connectedPimUserProvider
    ) {
    }

    public function __invoke(Request $request, string $clientId, bool $hasUserAuthenticationConsent = true): Response
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

        try {
            $this->createAppWithAuthorizationHandler->handle(new CreateAppWithAuthorizationCommand($clientId));
        } catch (InvalidAppAuthorizationRequest $exception) {
            $this->logger->warning(
                sprintf('App activation failed with validation error "%s"', $exception->getMessage())
            );

            return new JsonResponse([
                'errors' => $this->violationListNormalizer->normalize($exception->getConstraintViolationList()),
            ], Response::HTTP_BAD_REQUEST);
        }

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

        $scopes = ScopeList::fromScopeString($appAuthorization->scope);
        if ($scopes->hasScopeOpenId() && $hasUserAuthenticationConsent) {
            $authenticationScopes = $this->scopeFilter->filterAuthenticationScopes($scopes);
            $this->createUserConsentQuery->execute(
                $appAuthenticationUser->getPimUserId(),
                $appConfirmation->getAppId(),
                $authenticationScopes->getScopes(),
                $this->clock->now()
            );
        }

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $appAuthenticationUser
        );

        return new JsonResponse([
            'appId' => $appConfirmation->getAppId(),
            'userGroup' => $appConfirmation->getUserGroup(),
            'redirectUrl' => $redirectUrl,
        ]);
    }
}
