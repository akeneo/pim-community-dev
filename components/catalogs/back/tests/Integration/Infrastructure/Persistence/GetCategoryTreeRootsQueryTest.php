<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetCategoryTreeRootsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsQueryTest extends IntegrationTestCase
{
    private ?GetCategoryTreeRootsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCategoryTreeRootsQuery::class);
    }

    public function testItGetsCategoryTreeRoots(): void
    {
        $masterCategory = $this->getCategory('master');
        $tshirtCategory = $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $this->createCategory([
            'code' => 'tanktop',
            'parent' => 'tshirt',
            'labels' => ['en_US' => 'T-shirt']
        ]);

        $expectedMasterCategory = [
            'id' => $masterCategory->getId(),
            'code' => 'master',
            'label' => 'Master catalog',
            'isLeaf' => false,
        ];

        $expectedTshirtCategory = [
            'id' => $tshirtCategory->getId(),
            'code' => $tshirtCategory->getCode(),
            'label' => $tshirtCategory->getLabel(),
            'isLeaf' => false,
        ];

        $result = $this->query->execute();

        $this->assertEquals([$expectedMasterCategory, $expectedTshirtCategory], $result);
    }
}
