<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Apps;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
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
class GetAppActivateUrlController
{
    private GetAppQueryInterface $getAppQuery;
    private ClientProviderInterface $clientProvider;
    private AppUrlGenerator $appUrlGenerator;
    private SecurityFacade $security;
    private FeatureFlag $featureFlag;

    public function __construct(
        GetAppQueryInterface $getAppQuery,
        ClientProviderInterface $clientProvider,
        AppUrlGenerator $appUrlGenerator,
        SecurityFacade $security,
        FeatureFlag $featureFlag
    ) {
        $this->getAppQuery = $getAppQuery;
        $this->clientProvider = $clientProvider;
        $this->appUrlGenerator = $appUrlGenerator;
        $this->security = $security;
        $this->featureFlag = $featureFlag;
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        $app = $this->getAppQuery->execute($id);
        if (null === $app) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        $app = $app->withPimUrlSource($this->appUrlGenerator->getAppQueryParameters());

        $this->clientProvider->findOrCreateClient($app);

        return new JsonResponse([
            'url' => $app->getActivateUrl(),
        ]);
    }
}
