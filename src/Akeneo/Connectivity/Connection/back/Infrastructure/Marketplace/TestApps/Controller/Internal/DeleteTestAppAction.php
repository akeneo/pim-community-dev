<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
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
final class DeleteTestAppAction
{
    public function __construct(
        private FeatureFlag $appDevModeFeatureFlag,
        private SecurityFacade $security,
        private DeleteTestAppHandler $deleteTestAppHandler,
        private GetTestAppQueryInterface $getTestAppQuery,
        private DeleteAppHandler $deleteAppHandler,
    ) {
    }

    public function __invoke(Request $request, string $testAppId): Response
    {
        if (!$this->appDevModeFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $testAppData = $this->getTestAppQuery->execute($testAppId);
        if (null === $testAppData) {
            throw new NotFoundHttpException(\sprintf('Test app with id %s was not found.', $testAppId));
        }

        $this->deleteTestAppHandler->handle(new DeleteTestAppCommand($testAppId));

        if ($testAppData['connected']) {
            $this->deleteAppHandler->handle(new DeleteAppCommand($testAppId));
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
