<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoryTreeRootsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoryTreeRootsQuery
 */
class GetCategoryTreeRootsQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCategoryTreeRoots(): void
    {
        // master category exists as part of the minimal catalog
        $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $this->createCategory(['code' => 'skirt', 'labels' => ['fr_FR' => 'Jupe']]);
        $this->createCategory(['code' => 'tanktop', 'parent' => 'tshirt']);

        $expectedMasterCategory = [
            'code' => 'master',
            'label' => 'Master catalog',
            'isLeaf' => false,
        ];

        $expectedTshirtCategory = [
            'code' => 'tshirt',
            'label' => 'T-shirt',
            'isLeaf' => false,
        ];

        $expectedSkirtCategory = [
            'code' => 'skirt',
            'label' => '[skirt]',
            'isLeaf' => false,
        ];

        $result = self::getContainer()->get(GetCategoryTreeRootsQuery::class)->execute('en_US');

        $this->assertEquals([$expectedMasterCategory, $expectedTshirtCategory, $expectedSkirtCategory], $result);
    }
}
