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
class GetCatalogsActionTest extends IntegrationTestCase
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

    public function testItGetsPaginatedCatalogsByOwnerUsnermae(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);
        $user = $this->tokenStorage->getToken()->getUser();
        $username = $user->getUserIdentifier();

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $username,
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            $username,
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            $username,
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [
                'page' => 1,
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $firstPageResponse = $this->client->getResponse();
        $firstPagePayload = \json_decode($firstPageResponse->getContent(), true);

        Assert::assertEquals(200, $firstPageResponse->getStatusCode());
        Assert::assertCount(2, $firstPagePayload['_embedded']['items']);

        Assert::assertSame('27c53e59-ee6a-4215-a8f1-2fccbb67ba0d', $firstPagePayload['_embedded']['items'][0]['id']);
        Assert::assertSame('Store UK', $firstPagePayload['_embedded']['items'][0]['name']);
        Assert::assertSame(false, $firstPagePayload['_embedded']['items'][0]['enabled']);

        Assert::assertSame('db1079b6-f397-4a6a-bae4-8658e64ad47c', $firstPagePayload['_embedded']['items'][1]['id']);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient([]);

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItGetsBadRequestWithWrongPagination(): void
    {
        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);
        $user = $this->tokenStorage->getToken()->getUser();
        $username = $user->getUserIdentifier();

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $username,
        ));

        $this->client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [
                'page' => -1,
                'limit' => -1,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(400, $response->getStatusCode());
    }
}
