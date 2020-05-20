<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Repository;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\MonitoredRoutes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Routing\RouterInterface;

class MonitoredRoutesIntegration extends TestCase
{
    public function test_that_monitored_routes_exists(): void
    {
        /** @var RouterInterface */
        $router = $this->get('router');

        $collection = $router->getRouteCollection();

        foreach (MonitoredRoutes::ROUTES as $route) {
            Assert::assertNotNull(
                $collection->get($route),
                sprintf('The monitored route "%s" was not found in the routes definition.', $route)
            );
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
