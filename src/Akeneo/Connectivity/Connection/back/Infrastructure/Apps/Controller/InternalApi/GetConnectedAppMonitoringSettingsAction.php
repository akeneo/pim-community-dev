<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\AccessDeniedException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
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
final class GetConnectedAppMonitoringSettingsAction
{
    public function __construct(
        private FeatureFlag $featureFlag,
        private SecurityFacade $security,
        private FindAConnectionHandler $findAConnectionHandler,
        private ConnectedAppRepositoryInterface $connectedAppRepository,
    ) {
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedApp = $this->connectedAppRepository->findOneByConnectionCode($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $this->denyAccessUnlessGrantedToManage($connectedApp);

        $connection = $this->findAConnectionHandler->handle(new FindAConnectionQuery($connectionCode));

        if (null === $connection) {
            throw new NotFoundHttpException("Connection with connection code $connectionCode does not exist.");
        }

        return new JsonResponse([
            'flowType' => $connection->flowType(),
            'auditable' => $connection->auditable(),
        ]);
    }

    private function denyAccessUnlessGrantedToManage(ConnectedApp $app): void
    {
        if (!$app->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedException();
        }

        if ($app->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedException();
        }
    }
}
