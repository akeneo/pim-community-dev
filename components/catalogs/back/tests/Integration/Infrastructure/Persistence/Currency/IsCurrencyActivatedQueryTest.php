<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Currency;

use Akeneo\Catalogs\Application\Persistence\Currency\IsCurrencyActivatedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Currency\IsCurrencyActivatedQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Currency\IsCurrencyActivatedQuery
 */
class IsCurrencyActivatedQueryTest extends IntegrationTestCase
{
    private ?IsCurrencyActivatedQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(IsCurrencyActivatedQuery::class);
    }

    public function testItDefinesIfACurrencyIsActivated(): void
    {
        $isActivated = $this->query->execute('EUR');

        self::assertTrue($isActivated);
    }

    public function testItDefinesIfACurrencyIsNotActivated(): void
    {
        $isActivated = $this->query->execute('AUD');

        self::assertFalse($isActivated);
    }
}
