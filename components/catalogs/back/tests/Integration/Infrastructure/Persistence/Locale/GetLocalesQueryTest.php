<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Locale;

use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesQuery
 */
class GetLocalesQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsLocales(): void
    {
        // Locales are only activated when used in a channel
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $result = self::getContainer()->get(GetLocalesQuery::class)->execute();

        $expected = [
            ['code' => 'en_US', 'label' => 'English (United States)'],
            ['code' => 'fr_FR', 'label' => 'French (France)'],
        ];

        self::assertEquals($expected, $result);
    }
}
