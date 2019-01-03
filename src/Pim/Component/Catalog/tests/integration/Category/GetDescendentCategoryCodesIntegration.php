<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Category;

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
    // Validates that only the parent category code itself is returned.
    public function testGetDescendentCategoryCodesOfALeafCategory()
    {
        $parentCategory = $this->fetchCategory('master_accessories_scarves');
        $descendentCategoryCodes = $this->getDescendantCategoryCodesOf($parentCategory);

        Assert::assertSame(
            $descendentCategoryCodes,
            ['master_accessories_scarves']
        );
    }

    // Validates that category codes from all levels under the parent are returned.
    public function testGetDescendentCategoryCodesOfARoot()
    {
        $parentCategory = $this->fetchCategory('master');
        $descendentCategoryCodes = $this->getDescendantCategoryCodesOf($parentCategory);

        Assert::assertSame(
            $descendentCategoryCodes,
            [
                'master',
                'master_accessories',
                'master_accessories_belts',
                'master_accessories_bags',
                'master_accessories_sunglasses',
                'master_accessories_hats',
                'master_accessories_scarves',
                'master_men',
                'master_men_blazers',
                'master_men_blazers_deals',
                'master_men_pants',
                'master_men_pants_shorts',
                'master_men_pants_jeans',
                'master_men_shoes',
                'tshirts',
                'master_women',
                'master_women_blouses',
                'master_women_blouses_deals',
                'master_women_dresses',
                'master_women_shirts',
                'master_women_shoes',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
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
