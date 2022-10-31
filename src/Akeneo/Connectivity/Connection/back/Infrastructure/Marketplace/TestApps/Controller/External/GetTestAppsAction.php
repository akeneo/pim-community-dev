<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Api\Pagination\OffsetHalPaginator;
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
 *
 * @phpstan-import-type ExternalTestApp from GetTestAppsQueryInterface
 */
final class GetTestAppsAction
{
    public function __construct(
        private FeatureFlag $developerModeFeatureFlag,
        private SecurityFacade $security,
        private TokenStorageInterface $tokenStorage,
        private GetTestAppsQueryInterface $getTestAppsQuery,
        private OffsetHalPaginator $offsetPaginator,
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

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 100);

        $testApps = $this->getTestAppsQuery->execute($user->getId(), $page, $limit);

        return new JsonResponse($this->paginate($testApps, $page, $limit), Response::HTTP_OK);
    }

    /**
     * @param array<ExternalTestApp> $testApps
     * @return array<array-key, mixed>
     */
    private function paginate(array $testApps, int $page, int $limit): array
    {
        return $this->offsetPaginator->paginate($testApps, [
            'query_parameters' => [
                'page' => $page,
                'limit' => $limit,
            ],
            'list_route_name' => 'akeneo_connectivity_connection_marketplace_api_test_apps_list',
            'item_route_name' => 'akeneo_connectivity_connection_marketplace_api_test_apps_get',
            'item_route_parameter' => 'clientId',
            'item_identifier_key' => 'client_id',
        ], null);
    }
}
