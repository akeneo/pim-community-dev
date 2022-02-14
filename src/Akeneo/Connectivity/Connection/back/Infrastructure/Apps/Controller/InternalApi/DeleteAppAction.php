<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\AccessDeniedException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteAppAction
{
    private FeatureFlag $featureFlag;
    private SecurityFacade $security;
    private ConnectedAppRepositoryInterface $connectedAppRepository;
    private DeleteAppHandler $deleteAppHandler;

    public function __construct(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        ConnectedAppRepositoryInterface $connectedAppRepository,
        DeleteAppHandler $deleteAppHandler
    ) {
        $this->featureFlag = $featureFlag;
        $this->security = $security;
        $this->connectedAppRepository = $connectedAppRepository;
        $this->deleteAppHandler = $deleteAppHandler;
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
            throw new NotFoundHttpException(
                sprintf('Connected app with connection code "%s" does not exist.', $connectionCode)
            );
        }

        $this->denyAccessUnlessGrantedToManage($connectedApp);

        $this->deleteAppHandler->handle(new DeleteAppCommand($connectedApp->getId()));

        return new Response(null, Response::HTTP_NO_CONTENT);
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
