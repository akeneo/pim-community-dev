<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM\Repository\ExternalApi;

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
        $categories = $this->getRepository()->searchAfterOffset([], [], 20, 0);
        Assert::assertCount(12, $categories);
    }

    public function test_to_count_categories(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count([]);
        Assert::assertEquals(12, $count);
    }

    public function test_to_count_categories_with_search(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count(['parent' => [['operator' => '=', 'value' => 'clothes']]]);
        Assert::assertEquals(4, $count);
    }

    public function test_to_get_categories_with_limit(): void
    {
        $this->initFixtures();
        $categories = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 0);
        Assert::assertCount(2, $categories);
        Assert::assertEquals('accessories', $categories[0]->getCode());
        Assert::assertEquals('bob', $categories[1]->getCode());
    }

    public function test_to_search_categories_after_the_offset(): void
    {
        $this->initFixtures();
        $categories = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 4);
        Assert::assertCount(2, $categories);
        Assert::assertEquals('hat', $categories[0]->getCode());
        Assert::assertEquals('master', $categories[1]->getCode());
    }

    public function test_to_search_ordered_categories(): void
    {
        $this->initFixtures();

        $categoriesCodeAsc = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 3, 0);
        Assert::assertCount(3, $categoriesCodeAsc);
        Assert::assertEquals('accessories', $categoriesCodeAsc[0]->getCode());
        Assert::assertEquals('bob', $categoriesCodeAsc[1]->getCode());
        Assert::assertEquals('bracelet', $categoriesCodeAsc[2]->getCode());

        $categoriesCodeDesc = $this->getRepository()->searchAfterOffset([], ['code' => 'DESC'], 3, 0);
        Assert::assertCount(3, $categoriesCodeDesc);
        Assert::assertEquals('women', $categoriesCodeDesc[0]->getCode());
        Assert::assertEquals('skirts', $categoriesCodeDesc[1]->getCode());
        Assert::assertEquals('ring', $categoriesCodeDesc[2]->getCode());
    }

    public function test_to_search_categories_by_codes(): void
    {
        $this->initFixtures();

        $categories = $this->getRepository()->searchAfterOffset(
            ['code' => [['operator' => 'IN', 'value' => ['accessories', 'women', 'hat', 'mr_hankey']]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(3, $categories);
        Assert::assertEquals('accessories', $categories[0]->getCode());
        Assert::assertEquals('hat', $categories[1]->getCode());
        Assert::assertEquals('women', $categories[2]->getCode());
    }

    public function test_to_search_categories_by_parent(): void
    {
        $this->initFixtures();

        $categories = $this->getRepository()->searchAfterOffset(
            ['parent' => [['operator' => '=', 'value' => 'accessories']]],
            ['code' => 'ASC'],
            10,
            0
        );
        Assert::assertCount(5, $categories);
        Assert::assertEquals('bob', $categories[0]->getCode());
        Assert::assertEquals('bracelet', $categories[1]->getCode());
        Assert::assertEquals('hat', $categories[2]->getCode());
        Assert::assertEquals('melon', $categories[3]->getCode());
        Assert::assertEquals('ring', $categories[4]->getCode());
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
        $this->createCategory(['code' => 'accessories']);
        $this->createCategory(['code' => 'ring', 'parent' => 'accessories']);
        $this->createCategory(['code' => 'bracelet', 'parent' => 'accessories']);
        $this->createCategory(['code' => 'hat', 'parent' => 'accessories']);
        $this->createCategory(['code' => 'bob', 'parent' => 'hat']);
        $this->createCategory(['code' => 'melon', 'parent' => 'hat']);

        $this->createCategory(['code' => 'clothes']);
        $this->createCategory(['code' => 'men', 'parent' => 'clothes']);
        $this->createCategory(['code' => 'pants', 'parent' => 'men']);
        $this->createCategory(['code' => 'women', 'parent' => 'clothes']);
        $this->createCategory(['code' => 'skirts', 'parent' => 'women']);
    }
}
