<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal This is an undocumented API endpoint used for internal purposes only
 */
final class GetTestAppsAction
{
    public function __construct(
        private FeatureFlag $developerModeFeatureFlag,
        private SecurityFacade $security,
        private TokenStorageInterface $tokenStorage,
        private GetTestAppsQueryInterface $getTestAppsQuery,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->developerModeFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException('Developer mode disabled');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to manage test apps.');
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            throw new BadRequestHttpException('Invalid user token.');
        }

        $testApps = $this->getTestAppsQuery->execute($user->getId());

        return new JsonResponse($testApps, Response::HTTP_OK);
    }
}
