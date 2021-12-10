<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
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
final class GetAllConnectedAppScopeMessagesAction
{
    private FeatureFlag $featureFlag;
    private SecurityFacade $security;
    private ConnectedAppRepositoryInterface $connectedAppRepository;
    private ScopeMapperRegistry $scopeMapperRegistry;

    public function __construct(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        ConnectedAppRepositoryInterface $connectedAppRepository,
        ScopeMapperRegistry $scopeMapperRegistry
    ) {
        $this->featureFlag = $featureFlag;
        $this->security = $security;
        $this->connectedAppRepository = $connectedAppRepository;
        $this->scopeMapperRegistry = $scopeMapperRegistry;
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        $connectedApp = $this->connectedAppRepository->findOneByConnectionCode($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $scopeMessages = $this->scopeMapperRegistry->getMessages($connectedApp->getScopes());

        return new JsonResponse($scopeMessages);
    }
}
