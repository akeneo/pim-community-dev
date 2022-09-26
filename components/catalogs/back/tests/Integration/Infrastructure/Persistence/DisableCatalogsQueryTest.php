<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogsQueryInterface;
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
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $catalogIdFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $catalogIdUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->createCatalog($catalogIdFR, 'Store FR', 'shopifi');
        $this->createCatalog($catalogIdUK, 'Store UK', 'shopifi');

        $this->enableCatalog($catalogIdUS);
        $this->enableCatalog($catalogIdFR);
        $this->enableCatalog($catalogIdUK);

        $this->disableCatalogsQuery->execute([$catalogIdUS, $catalogIdFR]);

        $this->assertCatalogIsDisabled($catalogIdUS);
        $this->assertCatalogIsDisabled($catalogIdFR);
        $this->assertCatalogIsEnabled($catalogIdUK);
    }

    public function testItDoesNothingOnCatalogsAlreadyDisabled(): void
    {
        $this->createUser('shopifi');
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');

        $this->disableCatalogsQuery->execute([$catalogIdUS]);

        $this->assertCatalogIsDisabled($catalogIdUS);
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
