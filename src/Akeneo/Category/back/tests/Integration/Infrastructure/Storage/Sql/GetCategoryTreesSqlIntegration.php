<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreesSqlIntegration extends TestCase
{
    public function testGetCategoryTrees(): void
    {
        $this->createCategory(['code' => 'category_A']);
        $this->createCategory(['code' => 'category_A_1', 'parent' => 'master']);

        $categoryTrees = $this->get(GetCategoryTreesInterface::class)->__invoke();
        $this->assertCount(2, $categoryTrees);
        $this->assertSame('category_A', (string) $categoryTrees[0]->getCode());
        $this->assertSame('master', (string) $categoryTrees[1]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
