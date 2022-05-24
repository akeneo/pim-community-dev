<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;
    private ?CommandBus $commandBus;
    private ?TokenStorageInterface $tokenStorage;

    public function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);
        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDeletesTheCatalog(): void
    {
        $this->client = $this->getAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $this->tokenStorage->getToken()->getUser()->getId(),
        ));

        $this->client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(204, $response->getStatusCode());
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedClient([]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $this->tokenStorage->getToken()->getUser()->getId(),
        ));

        $this->client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $this->client = $this->getAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotBelongToCurrentUser(): void
    {
        $anotherUserId = $this->createUser('willy-mesnage')->getId();
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $anotherUserId,
        ));

        $this->client = $this->getAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            'DELETE',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }
}
