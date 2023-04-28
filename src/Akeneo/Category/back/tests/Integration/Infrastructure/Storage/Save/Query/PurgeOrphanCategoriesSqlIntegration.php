<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\PurgeOrphanCategories;
use Akeneo\Category\Infrastructure\Storage\Sql\PurgeOrphanCategoriesSql;

class PurgeOrphanCategoriesSqlIntegration extends CategoryTestCase
{
    public function testPurgeOrphanCategoriesFromDatabase(): void
    {
        $parent = $this->createOrUpdateCategory(
            code: 'parent',
        );
        $child1 = $this->createOrUpdateCategory(
            code: 'child1',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );
        $child2 = $this->createOrUpdateCategory(
            code: 'child2',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );
        $child3 = $this->createOrUpdateCategory(
            code: 'child3',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $child4 = $this->createOrUpdateCategory(
            code: 'child4',
            parentId: $child3->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $this->createOrUpdateCategory(
            code: 'child5',
            parentId: $child4->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $affectedRows = $this->get(PurgeOrphanCategories::class)->execute();
        $this->assertEquals(0, $affectedRows);

        $this->assertNotNull($child3->getParentId());

        $this->setCategoryParentIdToNull((string) $child3->getCode());
        $child3 = $this->get(GetCategoryInterface::class)->byCode('child3');
        $this->assertNull($child3->getParentId());

        $affectedRows = $this->get(PurgeOrphanCategories::class)->execute();
        $this->assertEquals(3, $affectedRows);

        $nonOrphanCategoriesGenerator = $this->get(GetCategoryInterface::class)->byCodes(['parent', 'child1', 'child2', 'child3', 'child4', 'child5']);
        $nonOrphanCategories= [];
        foreach ($nonOrphanCategoriesGenerator as $category) {
            $nonOrphanCategories[] = $category;
        }
        $expectedCategories = [
            $parent,
            $child1,
            $child2,
        ];

        $this->assertCount( 3, $nonOrphanCategories);
        $this->assertEqualsCanonicalizing($expectedCategories, $nonOrphanCategories);
    }

    private function setCategoryParentIdToNull(string $code): void
    {
        $sql = <<< SQL
            UPDATE pim_catalog_category
            SET parent_id = NULL
            WHERE code = :code;
        SQL;
        $this->get('database_connection')->executeQuery($sql, ['code' => $code]);
    }
}
