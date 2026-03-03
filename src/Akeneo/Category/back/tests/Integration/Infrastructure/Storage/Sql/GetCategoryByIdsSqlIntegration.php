<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryByIds;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoryByIdsSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItRetrievesCategoryByIds(): void
    {
        $categoryPrint = $this->createOrUpdateCategory('print');
        $categoryAccessories = $this->createOrUpdateCategory('accessories');
        $categoryClothes = $this->createOrUpdateCategory('clothes');

        /** @var Category[] $expectedCategories */
        $expectedCategories = [$categoryPrint, $categoryAccessories, $categoryClothes];

        $givenIds = array_map(fn ($category) => $category->getId()->getValue(), $expectedCategories);

        /** @var Category[] $categories */
        $categories = ($this->get(GetCategoryByIds::class))($givenIds);

        $this->assertIsArray($categories);
        $this->assertContainsOnlyInstancesOf(Category::class, $categories);
        $this->assertCount(3, $categories);
        $this->assertEquals((string)$expectedCategories[0]->getCode(), (string)$categories[0]->getCode());
        $this->assertEquals((string)$expectedCategories[1]->getCode(), (string)$categories[1]->getCode());
        $this->assertEquals((string)$expectedCategories[2]->getCode(), (string)$categories[2]->getCode());
    }

    public function testItNotRetrievesCategoryByIds(): void
    {
        $givenIds = [998, 999];

        /** @var Category[] $categories */
        $categories = ($this->get(GetCategoryByIds::class))($givenIds);

        $this->assertIsArray($categories);
        $this->assertCount(0, $categories);
    }
}
