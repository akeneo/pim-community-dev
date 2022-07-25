<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelsActionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsChannelsWithLocales(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $client->request(
            'GET',
            '/rest/catalogs/channels',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $channels = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedChannels = [
            [
                'code' => 'ecommerce',
                'label'=> '[ecommerce]',
                'locales' => [
                    ['code' => 'en_US', 'label' => 'English (United States)'],
                ],
            ],
        ];
        Assert::assertEquals($expectedChannels, $channels);
    }
}
