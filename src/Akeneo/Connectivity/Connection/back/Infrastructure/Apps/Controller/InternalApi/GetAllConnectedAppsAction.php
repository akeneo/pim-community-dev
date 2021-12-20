<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetAllConnectedAppsAction
{
    private FeatureFlag $featureFlag;
    private ConnectedAppRepositoryInterface $connectedAppRepository;

    public function __construct(
        FeatureFlag $featureFlag,
        ConnectedAppRepositoryInterface $connectedAppRepository
    ) {
        $this->featureFlag = $featureFlag;
        $this->connectedAppRepository = $connectedAppRepository;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedApps = $this->connectedAppRepository->findAll();

        return new JsonResponse(
            \array_map(fn (ConnectedApp $connectedApp) => $connectedApp->normalize(), $connectedApps)
        );
    }
}
