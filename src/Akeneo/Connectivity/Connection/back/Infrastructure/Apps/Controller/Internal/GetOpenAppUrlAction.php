<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOpenAppUrlAction
{
    public function __construct(
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private SecurityFacade $security,
        private FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        private GetAppQueryInterface $getAppQuery,
        private SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        private AppUrlGenerator $appUrlGenerator,
    ) {
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $connectedApp = $this->findOneConnectedAppByConnectionCodeQuery->execute($connectionCode);
        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $app = $this->getAppQuery->execute($connectedApp->getId());
        if (null === $app) {
            throw new \LogicException("App not found with connected app id \"{$connectedApp->getId()}\"");
        }

        $this->denyAccessUnlessGrantedToManageOrOpen();

        $app = $app->withPimUrlSource($this->appUrlGenerator->getAppQueryParameters());

        if ($connectedApp->hasOutdatedScopes() && $this->isGrantedToManage()) {
            $this->saveConnectedAppOutdatedScopesFlagQuery->execute($connectedApp->getId(), false);
        }

        return new JsonResponse(['url' => $app->getActivateUrl()]);
    }

    private function isGrantedToManage(): bool
    {
        return $this->security->isGranted('akeneo_connectivity_connection_manage_apps');
    }

    private function isGrantedToOpen(): bool
    {
        return $this->security->isGranted('akeneo_connectivity_connection_open_apps');
    }

    private function denyAccessUnlessGrantedToManageOrOpen(): void
    {
        if (!$this->isGrantedToManage() && !$this->isGrantedToOpen()) {
            throw new AccessDeniedHttpException();
        }
    }
}
