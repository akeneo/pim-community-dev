<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetChannelAction
 */
class GetChannelActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsChannel(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $client->request(
            'GET',
            '/rest/catalogs/channels/ecommerce',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $channel = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedChannel = [
            'code' => 'ecommerce',
            'label' => '[ecommerce]',
        ];
        Assert::assertEquals($expectedChannel, $channel);
    }
}
