<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
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
final class GetConnectionAction
{
    public function __construct(
        private FindAConnectionHandler $findAConnectionHandler,
        private SecurityFacade $securityFacade
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $query = new FindAConnectionQuery($request->get('code', ''));
        $connection = $this->findAConnectionHandler->handle($query);

        if (null === $connection || 'default' !== $connection->type()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($connection->normalize());
    }
}
