<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
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
class GetConnectedAppMonitoringSettingsAction
{
    public function __construct(
        private FeatureFlag $featureFlag,
        private SecurityFacade $security,
        private FindAConnectionHandler $findAConnectionHandler,
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

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedHttpException();
        }

        $connection = $this->findAConnectionHandler->handle(new FindAConnectionQuery($connectionCode));

        if (null === $connection) {
            throw new NotFoundHttpException("Connection with connection code $connectionCode does not exist.");
        }

        return new JsonResponse([
            'flow_type' => $connection->flowType(),
            'auditable' => $connection->auditable(),
        ]);
    }
}
