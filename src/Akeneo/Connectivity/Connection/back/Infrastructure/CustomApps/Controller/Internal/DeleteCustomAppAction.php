<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    public function __invoke(Request $request, string $customAppId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $customAppData = $this->getCustomAppQuery->execute($customAppId);
        if (null === $customAppData) {
            throw new NotFoundHttpException(\sprintf('Custom app with id %s was not found.', $customAppId));
        }

        $this->deleteCustomAppHandler->handle(new DeleteCustomAppCommand($customAppId));

        if ($customAppData['connected']) {
            $this->deleteAppHandler->handle(new DeleteAppCommand($customAppId));
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
