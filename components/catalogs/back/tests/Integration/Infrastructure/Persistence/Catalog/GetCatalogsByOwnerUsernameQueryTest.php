<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogsByOwnerUsernameQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogsByOwnerUsernameQuery
 */
class GetCatalogsByOwnerUsernameQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsPaginatedCatalogsByOwnerUsername(): void
    {
        $this->createUser('owner');
        $this->createUser('another_user');
        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $idUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';
        $idJP = '34478398-d77b-44d6-8a71-4d9ba4cb2c3b';

        $this->createCatalog($idUS, 'Store US', 'owner', isEnabled: false);
        $this->createCatalog($idFR, 'Store FR', 'owner', isEnabled: false);
        $this->createCatalog($idJP, 'Store JP', 'another_user');
        $this->createCatalog($idUK, 'Store UK', 'owner', isEnabled: false);

        $resultFirstPage = self::getContainer()->get(GetCatalogsByOwnerUsernameQuery::class)->execute('owner', 0, 2);
        $expectedFirstPage = [
            new Catalog($idUK, 'Store UK', 'owner', false),
            new Catalog($idUS, 'Store US', 'owner', false),
        ];
        $this->assertEquals($expectedFirstPage, $resultFirstPage);

        $resultSecondPage = self::getContainer()->get(GetCatalogsByOwnerUsernameQuery::class)->execute('owner', 2, 2);
        $expectedSecondPage = [
            new Catalog($idFR, 'Store FR', 'owner', false),
        ];
        $this->assertEquals($expectedSecondPage, $resultSecondPage);
    }
}
