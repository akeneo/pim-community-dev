<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\tests\integration\Category;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\CategoryInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetDescendentCategoryCodesIntegration extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $fixturesLoader = new CategoryTreeFixturesLoader($this->testKernel->getContainer());
        $fixturesLoader->givenTheCategoryTrees([
            'ecommerce' => [
                'ecommerce_accessories' => [
                    'ecommerce_accessories_belts'      => [],
                    'ecommerce_accessories_bags'       => [],
                    'ecommerce_accessories_sunglasses' => [],
                    'ecommerce_accessories_hats'       => [],
                    'ecommerce_accessories_scarves'    => [],
                ],
            ],
            'print' => [
                'print_accessories' => [],
            ],
        ]);
    }

    // Validates that nothing is returned when the parent is a leaf.
    public function testGetDescendentCategoryCodesOfALeafCategory()
    {
        $parentCategory = $this->fetchCategory('ecommerce_accessories_scarves');
        $descendentCategoryCodes = $this->getDescendantCategoryCodesOf($parentCategory);

        Assert::assertEmpty($descendentCategoryCodes);
    }

    // Validates that category codes from all levels under the parent are returned.
    public function testGetDescendentCategoryCodesOfARoot()
    {
        $parentCategory = $this->fetchCategory('ecommerce');
        $descendentCategoryCodes = $this->getDescendantCategoryCodesOf($parentCategory);

        Assert::assertSame(
            $descendentCategoryCodes,
            [
                'ecommerce_accessories',
                'ecommerce_accessories_belts',
                'ecommerce_accessories_bags',
                'ecommerce_accessories_sunglasses',
                'ecommerce_accessories_hats',
                'ecommerce_accessories_scarves',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function fetchCategory(string $code): CategoryInterface
    {
        return $this
            ->get('pim_catalog.repository.product_category')
            ->findOneByIdentifier($code)
        ;
    }

    private function getDescendantCategoryCodesOf(CategoryInterface $parentCategory): array
    {
        return $this->get('pim_catalog.query.get_descendent_category_codes')($parentCategory);
    }
}
