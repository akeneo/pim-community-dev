<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryBaseSql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class UpsertCategoryBaseSqlIntegration extends TestCase
{
    public function testInsertNewCategoryInDatabase(): void
    {
        /** @var UpsertCategoryBaseSql $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            null,
            new Code($categoryCode),
            LabelCollection::fromArray([]),
            null
        );

        $upsertCategoryBaseQuery->execute($category);
        $getCategory = $this->get(GetCategoryInterface::class);
        /** @var Category $result */
        $result = $getCategory->byCode((string)$category->getCode());

        $this->assertNotNull($result);
        $this->assertSame((string)$category->getCode(), (string)$result->getCode());
    }

    public function testUpdateExistingCategoryInDatabase(): void
    {
        /** @var UpsertCategoryBaseSql $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(UpsertCategoryBaseSql::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            null,
            new Code($categoryCode),
            LabelCollection::fromArray([]),
            null
        );

        $upsertCategoryBaseQuery->execute($category);
        $getCategory = $this->get(GetCategoryInterface::class);
        /** @var Category $createdCategory */
        $createdCategory = $getCategory->byCode((string)$category->getCode());
        $this->assertNotNull($createdCategory);

        $updatedCategory = new Category(
            null,
            new Code('updatedCode'),
            LabelCollection::fromArray([]),
            new CategoryId($createdCategory->getId()->getValue())
        );

        $upsertCategoryBaseQuery->execute($updatedCategory);
        $getCategory = $this->get(GetCategoryInterface::class);
        /** @var Category $editedCategoryData */
        $editedCategoryData = $getCategory->byCode((string)$updatedCategory->getCode());

        $this->assertNotNull($editedCategoryData);
        $this->assertSame((string)$updatedCategory->getCode(), (string)$editedCategoryData->getCode());
        $this->assertSame($updatedCategory->getParentId()?->getValue(), (int)$editedCategoryData->getParentId()->getValue());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
