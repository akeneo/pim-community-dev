<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
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
final class ConfirmAuthorizationAction
{
    public function __construct(
        private CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler,
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        private ViolationListNormalizer $violationListNormalizer,
        private SecurityFacade $security,
        private LoggerInterface $logger,
        private RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        private AppAuthorizationSessionInterface $appAuthorizationSession,
        private ConnectedPimUserProvider $connectedPimUserProvider,
        private ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        private GetAppQueryInterface $getAppQuery,
        private FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        private UpdateConnectedAppScopesWithAuthorizationHandler $updateConnectedAppScopesWithAuthorizationHandler,
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

        $this->denyAccessUnlessGrantedToManage();

        $connectedPimUserId = $this->connectedPimUserProvider->getCurrentUserId();

        try {
            $connectedApp = $this->findOneConnectedAppByIdQuery->execute($clientId);

            if (null === $connectedApp) {
                $this->createConnectedAppWithAuthorizationHandler->handle(new CreateConnectedAppWithAuthorizationCommand($clientId));
            } else {
                $this->updateConnectedAppScopesWithAuthorizationHandler->handle(new UpdateConnectedAppScopesWithAuthorizationCommand($clientId));
            }

            $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
            if (null === $appAuthorization) {
                throw new \LogicException('There is no active app authorization in session');
            }

            $this->consentAppAuthenticationHandler->handle(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId));
        } catch (InvalidAppAuthorizationRequestException | InvalidAppAuthenticationException $exception) {
            $this->logger->warning(
                \sprintf('App activation failed with validation error "%s"', $exception->getMessage())
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

    private function denyAccessUnlessGrantedToManage(): void
    {
        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }
    }
}
