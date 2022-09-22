<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\DisableCatalogsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\DisableCatalogsQuery;
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
        $uuidUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $uuidFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $uuidUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($uuidUS, 'Store US', 'shopifi');
        $this->createCatalog($uuidFR, 'Store FR', 'shopifi');
        $this->createCatalog($uuidUK, 'Store UK', 'shopifi');

        $this->enableCatalog($uuidUS);
        $this->enableCatalog($uuidFR);
        $this->enableCatalog($uuidUK);

        $this->disableCatalogsQuery->execute([$uuidUS, $uuidFR]);

        $this->assertCatalogIsDisabled($uuidUS);
        $this->assertCatalogIsDisabled($uuidFR);
        $this->assertCatalogIsEnabled($uuidUK);
    }

    public function testItDoesNothingOnCatalogsAlreadyDisabled(): void
    {
        $this->createUser('shopifi');
        $uuidUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($uuidUS, 'Store US', 'shopifi');

        $this->disableCatalogsQuery->execute([$uuidUS]);

        $this->assertCatalogIsDisabled($uuidUS);
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
