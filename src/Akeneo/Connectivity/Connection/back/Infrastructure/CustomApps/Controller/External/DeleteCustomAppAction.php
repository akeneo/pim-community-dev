<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal This is an undocumented API endpoint used for internal purposes only
 */
final class DeleteCustomAppAction
{
    public function __construct(
        private readonly SecurityFacade $security,
        private readonly DeleteCustomAppHandler $deleteCustomAppHandler,
        private readonly GetCustomAppQueryInterface $getCustomAppQuery,
        private readonly DeleteAppHandler $deleteAppHandler,
    ) {
    }

    public function __invoke(string $clientId): JsonResponse
    {
        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $customAppData = $this->getCustomAppQuery->execute($clientId);
        if (null === $customAppData) {
            throw new NotFoundHttpException(\sprintf('Test app with %s client_id was not found.', $clientId));
        }

        $this->deleteCustomAppHandler->handle(new DeleteCustomAppCommand($clientId));

        if ($customAppData['connected']) {
            $this->deleteAppHandler->handle(new DeleteAppCommand($clientId));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
