<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthorizationHandlerIntegration extends TestCase
{
    private RequestAppAuthorizationHandler $handler;
    private ClientManagerInterface $clientManager;
    private PropertyAccessor $propertyAccessor;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(RequestAppAuthorizationHandler::class);
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    private function createOAuth2Client(array $data): ClientInterface
    {
        $client = $this->clientManager->createClient();
        foreach ($data as $key => $value) {
            $this->propertyAccessor->setValue($client, $key, $value);
        }
        $this->clientManager->updateClient($client);

        return $client;
    }

    public function test_it_throws_when_the_client_id_is_not_valid(): void
    {
        $command = new RequestAppAuthorizationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            'code',
            '',
            '',
        );

        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->expectExceptionMessage('akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid');
        $this->handler->handle($command);
    }

    public function test_it_throws_when_the_response_type_is_not_code(): void
    {
        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);

        $command = new RequestAppAuthorizationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            'foo',
            '',
            '',
        );

        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->expectExceptionMessage('akeneo_connectivity.connection.connect.apps.constraint.response_type.must_be_code');
        $this->handler->handle($command);
    }

    public function test_it_throws_when_the_scope_is_too_long(): void
    {
        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);

        $command = new RequestAppAuthorizationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            'code',
            \str_repeat('a', 1001),
            '',
        );

        $this->expectException(InvalidAppAuthorizationRequestException::class);
        $this->expectExceptionMessage('akeneo_connectivity.connection.connect.apps.constraint.scope.too_long');
        $this->handler->handle($command);
    }
}
