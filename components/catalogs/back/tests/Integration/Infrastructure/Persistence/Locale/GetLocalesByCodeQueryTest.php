<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Locale;

use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesByCodeQuery
 */
class GetLocalesByCodeQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsPaginatedLocalesByCode(): void
    {
        // Locales are only activated when used in a channel
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $page1 = self::getContainer()->get(GetLocalesByCodeQuery::class)->execute(['en_US', 'fr_FR'], 1, 1);
        $page2 = self::getContainer()->get(GetLocalesByCodeQuery::class)->execute(['en_US', 'fr_FR'], 2, 1);
        $page3 = self::getContainer()->get(GetLocalesByCodeQuery::class)->execute(['en_US', 'fr_FR'], 3, 1);

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
        $page = self::getContainer()->get(GetLocalesByCodeQuery::class)->execute([], 1, 2);

        self::assertEquals([], $page);
    }
}
