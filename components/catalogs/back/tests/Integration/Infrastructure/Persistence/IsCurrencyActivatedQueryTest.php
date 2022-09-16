<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\IsCurrencyActivatedQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\IsCurrencyActivatedQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

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
