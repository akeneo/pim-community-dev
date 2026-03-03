<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAppConfirmationQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUser;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUserGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppConfirmationQueryIntegration extends TestCase
{
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private GetAppConfirmationQuery $query;
    private CreateConnection $createConnection;
    private ClientProvider $clientProvider;
    private CreateUserGroup $createUserGroup;
    private CreateUser $createUser;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->query = $this->get(GetAppConfirmationQuery::class);
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->createUserGroup = $this->get(CreateUserGroup::class);
        $this->createUser = $this->get(CreateUser::class);
    }

    private function createConnectedApp(string $appPublicId): void
    {
        $group = $this->createUserGroup->execute('userGroup_' . $appPublicId);

        $userId = $this->createUser->execute(
            'username_' . $appPublicId,
            'firstname_' . $appPublicId,
            [$group->getName()],
            ['ROLE_USER'],
            $appPublicId,
        );

        $client = $this->clientProvider->findOrCreateClient(
            App::fromWebMarketplaceValues([
                'id' => $appPublicId,
                'name' => 'testName',
                'logo' => 'testLogo',
                'author' => 'testAuthor',
                'url' => 'testUrl',
                'categories' => [],
                'activate_url' => 'testUrl',
                'callback_url' => 'testUrl',
            ])
        );

        $this->createConnection->execute(
            'connectionCode_' . $appPublicId,
            'Connector_' . $appPublicId,
            FlowType::OTHER,
            $client->getId(),
            $userId,
        );

        $this->createConnectedAppQuery->execute(
            new ConnectedApp(
                'connectedAppId_' . $appPublicId,
                'App',
                [],
                'connectionCode_' . $appPublicId,
                'http://www.example.com/path/to/logo',
                'author',
                'userGroup_' . $appPublicId,
                'username_' . $appPublicId,
                [],
                false,
                'partner'
            )
        );
    }

    public function test_it_returns_an_app_confirmation_for_a_valid_id(): void
    {
        $this->createConnectedApp('foo');

        $result = $this->query->execute('foo');

        $this->assertInstanceOf(AppConfirmation::class, $result);

        $normalized = $result->normalize();
        $this->assertEquals('connectedAppId_foo', $normalized['app_id']);
        $this->assertIsInt($normalized['user_id']);
        $this->assertEquals('userGroup_foo', $normalized['user_group']);
        $this->assertIsInt($normalized['fos_client_id']);
    }

    public function test_it_returns_null_for_an_invalid_id(): void
    {
        $result = $this->query->execute('bar');

        $this->assertNull($result);
    }
}
