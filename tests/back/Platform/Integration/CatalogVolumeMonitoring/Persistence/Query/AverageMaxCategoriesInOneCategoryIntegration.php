<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class AverageMaxCategoriesInOneCategoryIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfCategoriesInOneCategory()
    {
        //in minimal catalog we have one root category - so average is CEIL(AVG(0,4,6))
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_categories_in_one_category');
        $this->createCategoryWithSubCategories(4);
        $this->createCategoryWithSubCategories(6);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_categories_in_one_category', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfSubCategory
     * @return CategoryInterface
     */
    protected function createCategoryWithSubCategories(int $numberOfSubCategory) : CategoryInterface
    {
        $rootCategory = $this->createCategory([
            'code' => 'new_category_' . rand()
        ]);
        $this->get('validator')->validate($rootCategory);
        $this->get('pim_catalog.saver.category')->save($rootCategory);

        $i = 0;
        while ($i < $numberOfSubCategory) {
            $subCategory = $this->createCategory([
                'code' => 'new_category_' . rand(),
                'parent' => $rootCategory->getCode()
            ]);
            $i++;
            $this->get('validator')->validate($subCategory);
            $this->get('pim_catalog.saver.category')->save($subCategory);
        }

        return $rootCategory;
    }
}
