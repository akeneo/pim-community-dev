<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RedirectToEditConnectedAppAction
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly SecurityFacade $security,
        private readonly FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($id);
        if (null === $connectedApp) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGrantedToManage($connectedApp);

        return new RedirectResponse(
            '/#' . $this->router->generate('akeneo_connectivity_connection_connect_connected_apps_edit', [
                'connectionCode' => $connectedApp->getConnectionCode(),
            ])
        );
    }

    private function denyAccessUnlessGrantedToManage(ConnectedApp $app): void
    {
        if (!$this->isGrantedToManage($app)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function isGrantedToManage(ConnectedApp $app): bool
    {
        return $this->security->isGranted('akeneo_connectivity_connection_manage_apps');
    }
}
