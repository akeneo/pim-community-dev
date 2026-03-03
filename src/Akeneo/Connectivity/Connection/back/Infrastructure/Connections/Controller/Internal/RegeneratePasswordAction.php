<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
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
final class RegeneratePasswordAction
{
    public function __construct(
        private RegenerateConnectionPasswordHandler $regenerateConnectionPasswordHandler,
        private SecurityFacade $securityFacade,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new RegenerateConnectionPasswordCommand($request->get('code', ''));
        try {
            $password = $this->regenerateConnectionPasswordHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['password' => $password]);
    }
}
