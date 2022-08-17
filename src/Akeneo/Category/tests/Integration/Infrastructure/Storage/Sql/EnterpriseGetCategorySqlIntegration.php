<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoEnterprise\Category\Infrastructure\Storage\Sql\EnterpriseGetCategorySql;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnterpriseGetCategorySqlIntegration extends TestCase
{
    private CategoryDoctrine|Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);
    }

    public function testGetCategoryByCode(): void
    {
        $category = $this->get(EnterpriseGetCategorySql::class)->byCode($this->category->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertTrue($category->getPermissionCollection()->isViewable());
        $this->assertTrue($category->getPermissionCollection()->isEditable());
        $this->assertTrue($category->getPermissionCollection()->isOwned());
    }

    public function testGetCategoryById(): void
    {
        $category = $this->get(EnterpriseGetCategorySql::class)->byId($this->category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertTrue($category->getPermissionCollection()->isViewable());
        $this->assertTrue($category->getPermissionCollection()->isEditable());
        $this->assertTrue($category->getPermissionCollection()->isOwned());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
