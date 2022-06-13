<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

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
class GetCatalogActionTest extends IntegrationTestCase
{
    private ?KernelBrowser $client;
    private ?CommandBus $commandBus;

    public function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);
        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalog(): void
    {
        $this->client = $this->getAuthenticatedInternalApiClient('admin');

        $this->commandBus->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'admin',
        ));

        $this->client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());

        Assert::assertSame('ed30425c-d9cf-468b-8bc7-fa346f41dd07', $payload['id']);
        Assert::assertSame('Store FR', $payload['name']);
        Assert::assertSame(false, $payload['enabled']);
        Assert::assertSame('admin', $payload['owner_username']);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $this->client = $this->getAuthenticatedInternalApiClient('admin');

        $this->client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }
}
