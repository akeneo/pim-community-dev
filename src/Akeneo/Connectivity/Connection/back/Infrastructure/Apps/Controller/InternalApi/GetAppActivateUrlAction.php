<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAppActivateUrlAction
{
    public function __construct(
        private GetAppQueryInterface $getAppQuery,
        private ClientProviderInterface $clientProvider,
        private AppUrlGenerator $appUrlGenerator,
        private SecurityFacade $security,
        private FeatureFlag $featureFlag,
        private IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
    ) {
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

        if ($this->isConnectionsNumberLimitReachedQuery->execute()) {
            throw new BadRequestHttpException('App and connections limit reached');
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
