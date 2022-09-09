<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetLocalesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetLocalesByCodeQuery
 */
class GetLocalesByCodeQueryTest extends IntegrationTestCase
{
    public ?object $connection;
    private ?GetLocalesByCodeQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetLocalesByCodeQuery::class);
    }

    public function testItGetsLocalesByCode(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        // Locales are only activated when used in a channel
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $client->request(
            'GET',
            '/rest/catalogs/locales',
            [
                'codes' => 'en_US,fr_FR'
            ],
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
                'label'=> 'English (United States)',
            ],
            [
                'code' => 'fr_FR',
                'label'=> 'French (France)',
            ],
        ];
        Assert::assertEquals($expectedLocales, $locales);
    }

    public function testItGetsNoLocales(): void
    {
        $page = $this->query->execute([], 1, 2);

        self::assertEquals([], $page);
    }
}
