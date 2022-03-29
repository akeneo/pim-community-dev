<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationHandlerIntegration extends TestCase
{
    private UpdateConnectedAppScopesWithAuthorizationHandler $handler;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;
    private ConnectedAppLoader $connectedAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(UpdateConnectedAppScopesWithAuthorizationHandler::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function throwExceptionDataProvider(): array
    {
        return [
            'not blank' => [
                '',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.not_blank',
            ],
            'client id must be valid' => [
                'unknownId',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid',
            ],
            'client id must have an ongoing authorization' => [
                '0dfce574-2238-4b13-b8cc-8d257ce7645b',
                'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_have_ongoing_authorization',
            ],
        ];
    }

    public function scopesDataProvider(): array
    {
        return [
            'more scopes' => [
                ['read_products', 'write_categories'],
                ['delete_products', 'read_association_types', 'write_catalog_structure'],
            ],
            'less scopes' => [
                ['delete_products', 'read_association_types', 'write_catalog_structure'],
                ['read_products', 'write_categories'],
            ],
            'same scopes' => [
                ['read_products', 'write_categories'],
                ['read_products', 'write_categories'],
            ],
        ];
    }

    /**
     * @dataProvider throwExceptionDataProvider
     */
    public function test_it_throws_when_the_command_is_not_valid(string $clientId, $expectedMessage)
    {
        $appId = '0dfce574-2238-4b13-b8cc-8d257ce7645b';

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            $appId,
            'akeneo_print',
        );

        $command = new UpdateConnectedAppScopesWithAuthorizationCommand($clientId);

        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->handler->handle($command);
    }

    /**
     * @dataProvider scopesDataProvider
     */
    public function test_it_updates_scopes(array $oldScopes, array $newScopes)
    {
        $appId = '0dfce574-2238-4b13-b8cc-8d257ce7645b';

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            $appId,
            'akeneo_print',
            $oldScopes
        );

        $this->appAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            $appId,
            'code',
            \implode(' ', $newScopes),
            'http://anyurl.test'
        ));

        $foundApp = $this->findOneConnectedAppByIdQuery->execute($appId);
        Assert::assertEquals($oldScopes, $foundApp->getScopes());

        $this->handler->handle(new UpdateConnectedAppScopesWithAuthorizationCommand($appId));

        $foundApp = $this->findOneConnectedAppByIdQuery->execute($appId);
        Assert::assertEquals($newScopes, $foundApp->getScopes());
    }
}
