<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ExternalApi\CategoryRepository;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CategoryRepositoryApiResourceIntegration extends TestCase
{
    public function test_to_get_the_identifier_properties(): void
    {
        $properties = $this->getRepository()->getIdentifierProperties();
        Assert::assertEquals(['code'], $properties);
    }

    public function test_to_find_a_category_by_code(): void
    {
        $this->initFixtures();
        $category = $this->getRepository()->findOneByIdentifier('master');

        Assert::assertInstanceOf(Category::class, $category);
        Assert::assertEquals('master', $category->getCode());
    }

    public function test_to_get_categories(): void
    {
        $this->initFixtures();
        $categories = $this->getRepository()->searchAfterOffset([], [], 10, 0);
        Assert::assertCount(5, $categories);
    }

    public function test_to_count_categories(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count([]);
        Assert::assertEquals(5, $count);
    }

    public function test_to_count_categories_with_search(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count(['parent' => [['operator' => '=', 'value' => 'categoryA']]]);
        Assert::assertEquals(2, $count);
    }

    public function test_to_get_categories_with_limit(): void
    {
        $this->initFixtures();
        $categories = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 0);
        Assert::assertCount(2, $categories);
        Assert::assertEquals('categoryA', $categories[0]->getCode());
        Assert::assertEquals('categoryA1', $categories[1]->getCode());
    }

    public function test_to_search_categories_after_the_offset(): void
    {
        $this->initFixtures();
        $categories = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 2);
        Assert::assertCount(2, $categories);
        Assert::assertEquals('categoryA2', $categories[0]->getCode());
        Assert::assertEquals('categoryB', $categories[1]->getCode());
    }

    public function test_to_search_ordered_categories(): void
    {
        $this->initFixtures();

        $categoriesCodeAsc = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 3, 0);
        Assert::assertCount(3, $categoriesCodeAsc);
        Assert::assertEquals('categoryA', $categoriesCodeAsc[0]->getCode());
        Assert::assertEquals('categoryA1', $categoriesCodeAsc[1]->getCode());
        Assert::assertEquals('categoryA2', $categoriesCodeAsc[2]->getCode());

        $categoriesCodeDesc = $this->getRepository()->searchAfterOffset([], ['code' => 'DESC'], 3, 0);
        Assert::assertCount(3, $categoriesCodeDesc);
        Assert::assertEquals('master', $categoriesCodeDesc[0]->getCode());
        Assert::assertEquals('categoryB', $categoriesCodeDesc[1]->getCode());
        Assert::assertEquals('categoryA2', $categoriesCodeDesc[2]->getCode());
    }

    public function test_to_search_categories_by_codes(): void
    {
        $this->initFixtures();

        $categories = $this->getRepository()->searchAfterOffset(
            ['code' => [['operator' => 'IN', 'value' => ['categoryA', 'categoryB', 'categoryC']]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $categories);
        Assert::assertEquals('categoryA', $categories[0]->getCode());
        Assert::assertEquals('categoryB', $categories[1]->getCode());
    }

    public function test_to_search_categories_by_parent(): void
    {
        $this->initFixtures();

        $categories = $this->getRepository()->searchAfterOffset(
            ['parent' => [['operator' => '=', 'value' => 'categoryA']]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $categories);
        Assert::assertEquals('categoryA1', $categories[0]->getCode());
        Assert::assertEquals('categoryA2', $categories[1]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getRepository(): CategoryRepository
    {
        return $this->get('pim_api.repository.category');
    }

    private function initFixtures(): void
    {
        $this->createCategory(['code' => 'categoryA', 'parent' => 'master']);
        $this->createCategory(['code' => 'categoryA1', 'parent' => 'categoryA']);
        $this->createCategory(['code' => 'categoryA2', 'parent' => 'categoryA']);
        $this->createCategory(['code' => 'categoryB', 'parent' => 'master']);
    }
}
