<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandlerIntegration extends TestCase
{
    private FlagAppContainingOutdatedScopesHandler $handler;
    private ConnectedAppLoader $connectedAppLoader;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(FlagAppContainingOutdatedScopesHandler::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_flags_connected_app_as_with_outdated_scopes(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
            ['write_categories', 'read_channel_localization', 'read_products']
        );

        $connectedApp = $this->findOneConnectedAppByIdQuery->execute('outdated_app_id');

        $this->handler->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'write_categories read_products random noise read_channel_settings',
        ));

        $updatedApp = $this->findOneConnectedAppByIdQuery->execute('outdated_app_id');
        self::assertTrue($updatedApp->hasOutdatedScopes());
    }
}
