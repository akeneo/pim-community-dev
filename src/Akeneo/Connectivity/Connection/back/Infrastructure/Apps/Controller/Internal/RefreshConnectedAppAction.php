<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RefreshConnectedAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RefreshConnectedAppHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RefreshConnectedAppAction
{
    public function __construct(
        private FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        private RefreshConnectedAppHandler $refreshConnectedAppHandler,
    ) {
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedApp = $this->findOneConnectedAppByConnectionCodeQuery->execute($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $this->refreshConnectedAppHandler->handle(new RefreshConnectedAppCommand($connectedApp->getId()));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
