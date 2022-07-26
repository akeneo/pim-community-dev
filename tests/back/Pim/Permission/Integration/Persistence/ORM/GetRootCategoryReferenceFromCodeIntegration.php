<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoryReferenceFromCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetRootCategoryReferenceFromCodeIntegration extends TestCase
{
    private GetRootCategoryReferenceFromCode $query;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::$container->get(GetRootCategoryReferenceFromCode::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItReturnsTheCategoryEntity(): void
    {
        $category = $this->query->execute('master');

        $this->assertNotNull($category);
        $this->assertInstanceOf(Category::class, $category);
    }

    public function testItReturnsNullWhenTheCategoryCodeIsUnknown(): void
    {
        $category = $this->query->execute('not_an_existing_category');

        $this->assertNull($category);
    }
}
