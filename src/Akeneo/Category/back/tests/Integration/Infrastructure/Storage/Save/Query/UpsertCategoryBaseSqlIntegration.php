<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryBaseSql;

class UpsertCategoryBaseSqlIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryInDatabase(): void
    {
        /** @var UpsertCategoryBaseSql $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            id: null,
            code: new Code($categoryCode),
            attributes: ValueCollection::fromArray([]),
        );
        $upsertCategoryBaseQuery->execute($category);

        /** @var Category $categoryInserted */
        $categoryInserted = $this
            ->get(GetCategoryInterface::class)
            ->byCode((string)$category->getCode());

        $this->assertNotNull($categoryInserted);
        $this->assertSame((string)$category->getCode(), (string)$categoryInserted->getCode());
        $this->assertNotNull($category->getAttributes());
    }

    public function testUpdateExistingCategoryInDatabase(): void
    {
        $categoryCode = new Code('myCategory');
        $categoryInserted = $this->insertBaseCategory($categoryCode);

        // Update Category
        $categoryToUpdate = new Category(
            id: $categoryInserted->getId(),
            code: $categoryInserted->getCode(),
            parentId: new CategoryId($categoryInserted->getId()->getValue()),
            attributes: ValueCollection::fromArray([])
        );

        /** @var UpsertCategoryBaseSql $upsertCategoryBaseSql */
        $upsertCategoryBaseSql = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseSql::class);

        // Update Category previously inserted
        $upsertCategoryBaseSql->execute($categoryToUpdate);

        /** @var Category $updatedCategory */
        $updatedCategory = $this
            ->get(GetCategoryInterface::class)
            ->byCode((string)$categoryCode);

        $this->assertNotNull($updatedCategory);
        $this->assertSame((string)$categoryInserted->getCode(), (string)$updatedCategory->getCode());
        $this->assertNotSame($updatedCategory, $categoryInserted);
        $this->assertNotNull($updatedCategory->getAttributes());
        $this->assertNotNull($updatedCategory->getParentId());
    }
}
