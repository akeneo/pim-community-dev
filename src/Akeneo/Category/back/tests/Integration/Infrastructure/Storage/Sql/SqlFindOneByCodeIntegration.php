<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\FindCategoryByCode;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindOneByCodeIntegration extends TestCase
{
    public function testGetCategoryByCode(): void
    {
        $categoryCreated = $this->createCategory([
                'code' => 'socks',
                'labels' => ['fr_FR' => 'chaussettes']
            ]
        );

        $category = $this->get(FindCategoryByCode::class)($categoryCreated->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('socks', (string)$category->getCode());
        $this->assertSame('chaussettes', $category->getLabelCollection()->getLabel('fr_FR'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
