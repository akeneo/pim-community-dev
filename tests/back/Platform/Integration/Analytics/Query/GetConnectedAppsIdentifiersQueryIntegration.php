<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Platform\Bundle\AnalyticsBundle\Query\Sql\GetConnectedAppsIdentifiersQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppsIdentifiersQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private GetConnectedAppsIdentifiersQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get('pim_analytics.query.get_connected_apps_identifiers');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): ?Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_fetches_identifiers_of_connected_apps(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('00528c5a-9aef-4f9a-8a04-cdebf34176db', 'foo');
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('6a304013-35af-45f1-bf35-826b57c83893', 'bar');

        $result = $this->query->execute();
        Assert::assertEqualsCanonicalizing([
            '00528c5a-9aef-4f9a-8a04-cdebf34176db',
            '6a304013-35af-45f1-bf35-826b57c83893',
        ], $result);
    }
}
