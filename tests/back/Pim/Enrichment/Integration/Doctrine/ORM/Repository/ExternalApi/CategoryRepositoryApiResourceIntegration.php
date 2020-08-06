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
        $count = $this->getRepository()->count(['parent' => [['operator' => 'IN', 'value' => ['categoryA']]]]);
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
        $this->createCategory(['parent' => 'master', 'code' => 'categoryA']);
        $this->createCategory(['parent' => 'categoryA', 'code' => 'categoryA1']);
        $this->createCategory(['parent' => 'categoryA', 'code' => 'categoryA2']);
        $this->createCategory(['parent' => 'master', 'code' => 'categoryB']);
    }
}
