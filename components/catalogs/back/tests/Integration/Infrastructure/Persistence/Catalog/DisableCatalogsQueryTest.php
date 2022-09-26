<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\DisableCatalogsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\DisableCatalogsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableCatalogsQueryTest extends IntegrationTestCase
{
    private ?DisableCatalogsQueryInterface $disableCatalogsQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disableCatalogsQuery = self::getContainer()->get(DisableCatalogsQuery::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsByUUID(): void
    {
        $this->createUser('shopifi');
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $idUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($idUS, 'Store US', 'shopifi');
        $this->createCatalog($idFR, 'Store FR', 'shopifi');
        $this->createCatalog($idUK, 'Store UK', 'shopifi');

        $this->enableCatalog($idUS);
        $this->enableCatalog($idFR);
        $this->enableCatalog($idUK);

        $this->disableCatalogsQuery->execute([$idUS, $idFR]);

        $this->assertCatalogIsDisabled($idUS);
        $this->assertCatalogIsDisabled($idFR);
        $this->assertCatalogIsEnabled($idUK);
    }

    public function testItDoesNothingOnCatalogsAlreadyDisabled(): void
    {
        $this->createUser('shopifi');
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($idUS, 'Store US', 'shopifi');

        $this->disableCatalogsQuery->execute([$idUS]);

        $this->assertCatalogIsDisabled($idUS);
    }

    private function assertCatalogIsDisabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertFalse($catalog->isEnabled());
    }

    private function assertCatalogIsEnabled(string $id): void
    {
        $catalog = $this->getCatalog($id);
        $this->assertTrue($catalog->isEnabled());
    }
}
