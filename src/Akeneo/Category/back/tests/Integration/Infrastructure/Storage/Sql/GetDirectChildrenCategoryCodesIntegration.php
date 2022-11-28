<?php
declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Category\Infrastructure\Storage\Sql\GetDirectChildrenCategoryCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetDirectChildrenCategoryCodesIntegration extends TestCase
{
    private GetDirectChildrenCategoryCodes $getDirectChildrenCategoryCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getDirectChildrenCategoryCodes = $this->get(GetDirectChildrenCategoryCodesInterface::class);
        $categoryTreeFixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader');

        $categoryTreeFixturesLoader->givenTheCategoryTrees([
            'master_catalog' => [
                'cameras' => [
                    'digital_cameras' => [
                        'digital_camera1' => [],
                        'digital_camera2' => [],
                        'digital_camera3' => [],
                    ],
                    'camcorders' => [
                        'camcorder1' => [],
                        'camcorder2' => [],
                        'camcorder3' => [],
                    ],
                    'webcams' => [
                        'webcam1' => [],
                        'webcam2' => [],
                        'webcam3' => [],
                    ],
                ],
                'tvs_projectors' => [
                    'monitors' => [],
                    'led_tvs' => [],
                ],
                'audio_video' => [
                ],
            ],
            'isolated_catalog' => [],
        ]);
    }

    public function testGetDirectChildrenOfARootCategory(): void
    {
        $categoryId = $this->fetchCategoryId('master_catalog');
        $children = $this->getDirectChildrenCategoryCodes->execute($categoryId);

        Assert::assertCount(3, $children);
        $this->assertCategoryCodeIsInPosition($children, 'cameras', 1);
        $this->assertCategoryCodeIsInPosition($children, 'tvs_projectors', 2);
        $this->assertCategoryCodeIsInPosition($children, 'audio_video', 3);
    }

    public function testGetDirectChildrenOfALeafCategory(): void
    {
        $categoryId = $this->fetchCategoryId('audio_video');
        $children = $this->getDirectChildrenCategoryCodes->execute($categoryId);

        Assert::assertCount(0, $children);
    }

    public function testGetDirectChildrenOfACategory(): void
    {
        $categoryId = $this->fetchCategoryId('webcams');
        $children = $this->getDirectChildrenCategoryCodes->execute($categoryId);

        Assert::assertCount(3, $children);
        $this->assertCategoryCodeIsInPosition($children, 'webcam1', 1);
        $this->assertCategoryCodeIsInPosition($children, 'webcam2', 2);
        $this->assertCategoryCodeIsInPosition($children, 'webcam3', 3);
    }

    public function testGetDirectChildrenOfIsolatedCategory(): void
    {
        $categoryId = $this->fetchCategoryId('isolated_catalog');
        $children = $this->getDirectChildrenCategoryCodes->execute($categoryId);

        Assert::assertCount(0, $children);
    }

    private function fetchCategoryId(string $code): int
    {
        return (int) $this->get('database_connection')->fetchOne('SELECT id FROM pim_catalog_category WHERE code= :code', ['code' => $code]);
    }

    /**
     * @param array<string, array{code: string, row_num: int}> $children
     */
    private function assertCategoryCodeIsInPosition(array $children, string $categoryCode, int $position): void
    {
        Assert::assertEquals($position, $children[$categoryCode]['row_num']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
