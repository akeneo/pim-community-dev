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
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCatalogAction
 */
class GetCatalogActionTest extends IntegrationTestCase
{
    public ?object $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalog(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'admin',
            isEnabled: false,
            catalogProductValueFilters: [
                'channel' => ['print', 'ecommerce'],
            ],
        );

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());

        Assert::assertSame('ed30425c-d9cf-468b-8bc7-fa346f41dd07', $payload['id']);
        Assert::assertSame('Store FR', $payload['name']);
        Assert::assertSame(false, $payload['enabled']);
        Assert::assertSame('admin', $payload['owner_username']);
        Assert::assertSame([
            [
                'field' => 'enabled',
                'value' => true,
                'operator' => '=',
            ],
        ], $payload['product_selection_criteria']);
        Assert::assertSame([
            'channel' => ['print', 'ecommerce'],
        ], $payload['product_value_filters']);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );
        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }
}
