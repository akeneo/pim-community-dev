<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Save\Query;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTrait;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\SqlUpsertCategoryBase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class SqlUpsertCategoryBaseIntegration extends TestCase
{
    use CategoryTrait;

    public function testInsertNewCategoryInDatabase()
    {
        /** @var SqlUpsertCategoryBase $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(SqlUpsertCategoryBase::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            null,
            new Code($categoryCode),
            LabelCollection::fromArray([]),
            null
        );

        $upsertCategoryBaseQuery->execute($category);
        $result = $this->getCategoryBaseDataByCode((string) $category->getCode());

        $this->assertNotNull($result);
        $this->assertSame((string) $category->getCode(), $result['code']);
        $this->assertSame($result['id'], $result['root']);
    }

    public function testUpdateExistingCategoryInDatabase()
    {
        /** @var SqlUpsertCategoryBase $upsertCategoryBaseQuery */
        $upsertCategoryBaseQuery = $this->get(UpsertCategoryBase::class);
        $this->assertEquals(SqlUpsertCategoryBase::class, $upsertCategoryBaseQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            null,
            new Code($categoryCode),
            LabelCollection::fromArray([]),
            null
        );

        $upsertCategoryBaseQuery->execute($category);
        $createdCategoryData = $this->getCategoryBaseDataByCode((string) $category->getCode());

        $this->assertNotNull($createdCategoryData);

        $updatedCategory = new Category(
            null,
            new Code('updatedCode'),
            LabelCollection::fromArray([]),
            new CategoryId((int) $createdCategoryData['id'])
        );

        $upsertCategoryBaseQuery->execute($updatedCategory);
        $editedCategoryData = $this->getCategoryBaseDataByCode((string) $updatedCategory->getCode());

        $this->assertNotNull($editedCategoryData);
        $this->assertSame((string) $updatedCategory->getCode(), $editedCategoryData['code']);
        $this->assertSame($updatedCategory->getParentId()->getValue(), (int) $editedCategoryData['parent_id']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
