<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetLocalesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

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

    public function testItGetsPaginatedLocalesByCode(): void
    {
        // Locales are only activated when used in a channel
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $page1 = $this->query->execute(['en_US', 'fr_FR'], 1, 1);
        $page2 = $this->query->execute(['en_US', 'fr_FR'], 2, 1);
        $page3 = $this->query->execute(['en_US', 'fr_FR'], 3, 1);

        $expectedPage1 = [
            [
                'code' => 'en_US',
                'label' => 'English (United States)',
            ],
        ];
        $expectedPage2 = [
            [
                'code' => 'fr_FR',
                'label' => 'French (France)',
            ],
        ];
        $expectedPage3 = [];

        self::assertEquals($expectedPage1, $page1);
        self::assertEquals($expectedPage2, $page2);
        self::assertEquals($expectedPage3, $page3);
    }

    public function testItGetsNoLocales(): void
    {
        $page = $this->query->execute([], 1, 2);

        self::assertEquals([], $page);
    }
}
