<?php
declare(strict_types=1);

namespace Akeneo\Test\Category\Integration;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\GetDirectChildrenCategoryCodes;
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

        $this->getDirectChildrenCategoryCodes = $this->get(
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\GetDirectChildrenCategoryCodes'
        );
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

    public function testGetDirectChildrenOfARootCategory()
    {
        $category = $this->fetchCategory('master_catalog');
        $children = $this->getDirectChildrenCategoryCodes->execute($category->getId());

        Assert::assertCount(3, $children);
        $this->assertCategoryCodeIsInPosition($children, 'cameras', 1);
        $this->assertCategoryCodeIsInPosition($children, 'tvs_projectors', 2);
        $this->assertCategoryCodeIsInPosition($children, 'audio_video', 3);
    }

    public function testGetDirectChildrenOfALeafCategory()
    {
        $category = $this->fetchCategory('audio_video');
        $children = $this->getDirectChildrenCategoryCodes->execute($category->getId());

        Assert::assertCount(0, $children);
    }

    public function testGetDirectChildrenOfACategory()
    {
        $category = $this->fetchCategory('webcams');
        $children = $this->getDirectChildrenCategoryCodes->execute($category->getId());

        Assert::assertCount(3, $children);
        $this->assertCategoryCodeIsInPosition($children, 'webcam1', 1);
        $this->assertCategoryCodeIsInPosition($children, 'webcam2', 2);
        $this->assertCategoryCodeIsInPosition($children, 'webcam3', 3);
    }

    public function testGetDirectChildrenOfIsolatedCategory()
    {
        $category = $this->fetchCategory('isolated_catalog');
        $children = $this->getDirectChildrenCategoryCodes->execute($category->getId());

        Assert::assertCount(0, $children);
    }

    private function fetchCategory(string $code): CategoryInterface
    {
        return $this
            ->get('pim_catalog.repository.product_category')
            ->findOneByIdentifier($code);
    }

    private function assertCategoryCodeIsInPosition(array $children, string $categoryCode, int $position): void
    {
        Assert::assertEquals($position, $children[$categoryCode]['row_num']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
