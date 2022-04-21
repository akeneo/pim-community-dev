<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteTestAppAction
{
    public function __construct(
        private FeatureFlag $developerModeFeatureFlag,
        private SecurityFacade $security,
        private DeleteTestAppHandler $deleteTestAppHandler,
        private GetTestAppQueryInterface $getTestAppQuery,
        private DeleteAppHandler $deleteAppHandler,
    ) {
    }

    public function __invoke(string $clientId): JsonResponse
    {
        if (!$this->developerModeFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $testAppData = $this->getTestAppQuery->execute($clientId);
        if (null === $testAppData) {
            throw new NotFoundHttpException(\sprintf('Test app with %s client_id was not found.', $clientId));
        }

        $this->deleteTestAppHandler->handle(new DeleteTestAppCommand($clientId));

        if ($testAppData['connected'] ?? false) {
            $this->deleteAppHandler->handle(new DeleteAppCommand($clientId));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
