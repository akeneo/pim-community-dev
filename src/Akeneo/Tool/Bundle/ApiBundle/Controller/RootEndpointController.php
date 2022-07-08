<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Controller;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * Root endpoint to show all API routes
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootEndpointController
{
    public function __construct(private Router $router, private FeatureFlags $featureFlags)
    {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $routes = $this->router->getRouteCollection();

        $apiRoutes = [
            'host'           => $request->getSchemeAndHttpHost(),
            'authentication' => [],
            'routes'         => []
        ];

        $routes->remove($request->attributes->get('_route'));

        foreach ($routes as $key => $route) {
            if (0 === strpos($route->getPath(), '/api')) {
                $feature = $route->getDefault('_feature');
                if ($feature !== null  && !$this->featureFlags->isEnabled($feature)) {
                    continue;
                }

                $shouldBelisted = $route->getDefault('_list_in_root_endpoint');
                if (false === $shouldBelisted) {
                    continue;
                }

                $type = 0 === strpos($route->getPath(), '/api/oauth') ? 'authentication' : 'routes';

                $apiRoutes[$type][$key] = [
                    'route'   => $route->getPath(),
                    'methods' => $route->getMethods()
                ];
            }
        }

        return new JsonResponse($apiRoutes);
    }
}
