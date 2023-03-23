<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Currency;

use Akeneo\Catalogs\Application\Persistence\Currency\GetChannelCurrenciesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Currency\GetChannelCurrenciesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Currency\GetChannelCurrenciesQuery
 */
class GetChannelCurrenciesQueryTest extends IntegrationTestCase
{
    private ?GetChannelCurrenciesQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetChannelCurrenciesQuery::class);
    }

    public function testItGetsChannelCurrency(): void
    {
        $this->createChannel('print', ['en_US'], ['USD', 'EUR', 'GBP']);
        $this->assertEquals(['USD', 'EUR', 'GBP'], $this->query->execute('print'));
    }

    public function testItGetsAnEmptyList(): void
    {
        $this->createChannel('print', ['en_US'], []);
        $this->assertEmpty($this->query->execute('print'));
    }

    public function testItThrowsAnExceptionWhenChannelCodeIsWrong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel \'unknown_channel_code\' not found');

        $this->query->execute('unknown_channel_code');
    }
}
