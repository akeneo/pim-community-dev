<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreesSqlIntegration extends TestCase
{
    private CategoryDoctrine|Category $categoryParent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryParent = $this->createCategory(['code' => 'categoryParent']);
        $this->createCategory(['code' => 'categoryChild', 'parent' => 'categoryParent']);
    }

    public function testGetAllCategoryTrees(): void
    {
        $categoryTrees = $this->get(GetCategoryTreesInterface::class)->getAll();
        $this->assertCount(2, $categoryTrees);
        $this->assertSame('categoryParent', (string) $categoryTrees[0]->getCode());
        $this->assertSame('master', (string) $categoryTrees[1]->getCode());
    }

    public function testGetCategoryTreesByIds(): void
    {
        $categoryTrees = $this->get(GetCategoryTreesInterface::class)->byIds([$this->categoryParent->getId()]);
        $this->assertSame('categoryParent', (string) $categoryTrees[0]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
