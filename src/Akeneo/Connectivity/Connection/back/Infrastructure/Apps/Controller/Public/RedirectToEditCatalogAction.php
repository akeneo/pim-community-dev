<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBusInterface;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
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
final class RedirectToEditCatalogAction
{
    public function __construct(
        private RouterInterface $router,
        private SecurityFacade $security,
        private QueryBusInterface $catalogQueryBus,
        private FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
    ) {
    }

    public function __invoke(string $id): Response
    {
        try {
            $catalog = $this->catalogQueryBus->execute(new GetCatalogQuery($id));
        } catch (\Exception) {
            throw new NotFoundHttpException();
        }

        if (null === $catalog) {
            throw new NotFoundHttpException();
        }

        $connectedApp = $this->findOneConnectedAppByUserIdentifierQuery->execute($catalog->getOwnerUsername());
        if (null === $connectedApp) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGrantedToManage();

        return new RedirectResponse(
            '/#' . $this->router->generate('akeneo_connectivity_connection_connect_connected_apps_catalogs_edit', [
                'connectionCode' => $connectedApp->getConnectionCode(),
                'catalogId' => $catalog->getId(),
            ])
        );
    }

    private function denyAccessUnlessGrantedToManage(): void
    {
        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }
    }
}
