<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DeleteConnectionAction
{
    public function __construct(
        private DeleteConnectionHandler $deleteConnectionHandler,
        private SecurityFacade $securityFacade,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteConnectionCommand($request->get('code', ''));
        try {
            $this->deleteConnectionHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
