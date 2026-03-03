<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryChildrenIds;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryChildrenIdsSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItRetrievesCategoryChildrenIds(): void
    {
        $categoryClothes = $this->createCategory(['code' => 'clothes']);
        $categoryPants = $this->createCategory(['code' => 'pants', 'parent' => 'clothes']);
        $categoryJeans = $this->createCategory(['code' => 'jeans', 'parent' => 'pants']);

        $categoryChildrenIds = ($this->get(GetCategoryChildrenIds::class))($categoryClothes->getId());

        $this->assertSame([
            0 => $categoryPants->getId(),
            1 => $categoryJeans->getId()
        ], $categoryChildrenIds);
    }

    public function testItDoesNotRetrieveCategoryChildrenIds(): void
    {
        $categoryAccessories = $this->createCategory(['code' => 'accessories']);

        $categoryChildrenIds = ($this->get(GetCategoryChildrenIds::class))($categoryAccessories->getId());

        $this->assertEmpty($categoryChildrenIds);
    }
}
