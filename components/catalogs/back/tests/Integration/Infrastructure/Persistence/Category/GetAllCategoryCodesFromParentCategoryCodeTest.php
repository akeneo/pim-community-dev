<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetAllCategoryCodesFromParentCategoryCode;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Category\GetAllCategoryCodesFromParentCategoryCode
 */
class GetAllCategoryCodesFromParentCategoryCodeTest extends IntegrationTestCase
{
    private ?GetAllCategoryCodesFromParentCategoryCode $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetAllCategoryCodesFromParentCategoryCode::class);
    }

    public function testItGetsAllCategoryCodesFromParentCategoryCode(): void
    {
        $this->createCategory(['code' => 'clothes']);
        $this->createCategory(['code' => 'tshirt', 'parent' => 'clothes']);
        $this->createCategory(['code' => 'pants', 'parent' => 'clothes']);
        $this->createCategory(['code' => 'shorts', 'parent' => 'pants']);

        $this->createCategory(['code' => 'car']);
        $this->createCategory(['code' => 'exterior', 'parent' => 'car']);
        $this->createCategory(['code' => 'interior', 'parent' => 'car']);
        $this->createCategory(['code' => 'tires', 'parent' => 'exterior']);
        $this->createCategory(['code' => 'dashcam', 'parent' => 'interior']);

        $codes = $this->query->execute('clothes');
        $expectedCodes = ['clothes', 'tshirt', 'pants', 'shorts'];
        $this->assertEquals($expectedCodes, $codes);

        $codes = $this->query->execute('pants');
        $expectedCodes = ['pants', 'shorts'];
        $this->assertEquals($expectedCodes, $codes);

        $codes = $this->query->execute('car');
        $expectedCodes = ['car', 'exterior', 'interior', 'tires', 'dashcam'];
        $this->assertEquals($expectedCodes, $codes);

        $codes = $this->query->execute('wrong_code');
        $expectedCodes = [];
        $this->assertEquals($expectedCodes, $codes);
    }
}
