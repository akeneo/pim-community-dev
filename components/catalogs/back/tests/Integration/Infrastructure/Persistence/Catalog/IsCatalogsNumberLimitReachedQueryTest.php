<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\IsCatalogsNumberLimitReachedQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCatalogsNumberLimitReachedQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTrueWhenTheCatalogsNumberLimitIsReached(): void
    {
        $this->createUser('shopifi');
        $limit = self::getContainer()->getParameter('akeneo_catalog.max_number_of_catalogs_per_user');

        $this->assertFalse(self::getContainer()->get(IsCatalogsNumberLimitReachedQuery::class)->execute('shopifi'));

        for ($i = 0; $i < $limit; $i++) {
            $this->createCatalog(
                Uuid::uuid4()->toString(),
                "Store $i",
                'shopifi',
            );
        }

        $this->assertTrue(self::getContainer()->get(IsCatalogsNumberLimitReachedQuery::class)->execute('shopifi'));
    }
}
