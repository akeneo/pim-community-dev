<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetLocalesAction
 */
class GetLocalesActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsLocales(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $client->request(
            'GET',
            '/rest/catalogs/locales',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $locales = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedLocales = [
            [
                'code' => 'en_US',
                'label' => 'English (United States)',
            ],
        ];
        Assert::assertEquals($expectedLocales, $locales);
    }

    public function testItGetsLocalesByCode(): void
    {
        // Locales are only activated when used in a channel
        $this->createChannel('mobile', ['fr_FR', 'en_US', 'de_DE']);

        $client = $this->getAuthenticatedInternalApiClient();

        $client->request(
            'GET',
            '/rest/catalogs/locales?codes=en_US,fr_FR',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());
        $locales = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expectedLocales = [
            [
                'code' => 'en_US',
                'label' => 'English (United States)',
            ],
            [
                'code' => 'fr_FR',
                'label' => 'French (France)',
            ],
        ];
        Assert::assertEquals($expectedLocales, $locales);
    }
}
