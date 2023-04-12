<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Currency;

use Akeneo\Catalogs\Infrastructure\Persistence\Currency\GetCurrenciesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Currency\GetCurrenciesQuery
 */
class GetCurrenciesQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCurrencies(): void
    {
        self::assertEquals(['EUR', 'USD'], self::getContainer()->get(GetCurrenciesQuery::class)->execute());
    }
}
