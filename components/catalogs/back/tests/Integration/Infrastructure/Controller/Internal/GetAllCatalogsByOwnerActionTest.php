<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetAllCatalogsByOwnerAction
 */
class GetAllCatalogsByOwnerActionTest extends IntegrationTestCase
{
    private ?CommandBus $commandBus;

    public function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);
        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalogsByOwner(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'admin',
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));
        $this->commandBus->execute(new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            'admin',
        ));

        $client->request(
            'GET',
            '/rest/catalogs',
            [
                'owner' => 'admin',
            ],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(3, $payload);

        Assert::assertSame('27c53e59-ee6a-4215-a8f1-2fccbb67ba0d', $payload[0]['id']);
        Assert::assertSame('Store UK', $payload[0]['name']);
        Assert::assertSame(false, $payload[0]['enabled']);
        Assert::assertSame('admin', $payload[0]['owner_username']);

        Assert::assertSame('db1079b6-f397-4a6a-bae4-8658e64ad47c', $payload[1]['id']);
        Assert::assertSame('ed30425c-d9cf-468b-8bc7-fa346f41dd07', $payload[2]['id']);
    }

    public function testItDoesNotGetCatalogsOfOtherUsers(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'admin',
        ));

        $client->request(
            'GET',
            '/rest/catalogs',
            [
                'owner' => 'another_user',
            ],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(0, $payload);
    }

    public function testItGetsBadRequestWithMissingOwnerParameter(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'GET',
            '/rest/catalogs',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );
        $response = $client->getResponse();

        Assert::assertEquals(400, $response->getStatusCode());
    }
}
