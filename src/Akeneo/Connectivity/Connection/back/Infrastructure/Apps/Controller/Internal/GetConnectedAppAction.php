<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
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
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetConnectedAppAction
{
    public function __construct(
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private SecurityFacade $security,
        private FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
    ) {
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedApp = $this->findOneConnectedAppByConnectionCodeQuery->execute($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $this->denyAccessUnlessGrantedToManage($connectedApp);

        return new JsonResponse($connectedApp->normalize());
    }

    private function denyAccessUnlessGrantedToManage(ConnectedApp $connectedApp): void
    {
        if (!$connectedApp->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        if ($connectedApp->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }
    }
}
