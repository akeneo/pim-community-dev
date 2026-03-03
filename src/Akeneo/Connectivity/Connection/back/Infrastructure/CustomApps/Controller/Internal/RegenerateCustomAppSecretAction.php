<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\RegenerateCustomAppSecretCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\RegenerateCustomAppSecretHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegenerateCustomAppSecretAction
{
    public function __construct(
        private readonly SecurityFacade $security,
        private readonly GetCustomAppQueryInterface $getCustomAppQuery,
        private readonly GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
        private readonly RegenerateCustomAppSecretHandler $regenerateCustomAppSecretHandler,
    ) {
    }

    public function __invoke(Request $request, string $customAppId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $customApp = $this->getCustomAppQuery->execute($customAppId);
        if (null === $customApp) {
            throw new NotFoundHttpException(\sprintf('Custom app with id %s was not found.', $customAppId));
        }

        $this->regenerateCustomAppSecretHandler->handle(new RegenerateCustomAppSecretCommand($customAppId));

        $secret = $this->getCustomAppSecretQuery->execute($customAppId);

        return new JsonResponse($secret);
    }
}
