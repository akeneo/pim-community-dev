<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
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
final class ConfirmAuthenticationAction
{
    public function __construct(
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        private SecurityFacade $security,
        private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private ConnectedPimUserProvider $connectedPimUserProvider,
        private ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        private LoggerInterface $logger,
        private ViolationListNormalizer $violationListNormalizer,
        private GetAppQueryInterface $getAppQuery,
    ) {
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $app = $this->getAppQuery->execute($clientId);
        if (null === $app) {
            return new JsonResponse([
                'errors' => [
                    [
                        'message' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                    ],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGrantedToOpen();

        $connectedPimUserId = $this->connectedPimUserProvider->getCurrentUserId();

        try {
            $this->consentAppAuthenticationHandler->handle(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId));
        } catch (InvalidAppAuthenticationException $exception) {
            $this->logger->warning(
                \sprintf('App activation failed with validation error "%s"', $exception->getMessage())
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

        $redirectUrl = $this->redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $connectedPimUserId
        );

        return new JsonResponse([
            'redirectUrl' => $redirectUrl,
        ]);
    }

    private function denyAccessUnlessGrantedToOpen(): void
    {
        if (!$this->security->isGranted('akeneo_connectivity_connection_open_apps')) {
            throw new AccessDeniedHttpException();
        }
    }
}
