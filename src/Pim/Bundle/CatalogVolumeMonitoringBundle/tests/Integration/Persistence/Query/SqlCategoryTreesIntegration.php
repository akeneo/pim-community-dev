<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;

class SqlCategoryTreesIntegration extends BuilderQueryTestCase
{
    public function testGetCountOfCategoryTrees()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.category_trees');
        $this->createCategoriesWithChild(3, 2);

        $volume = $query->fetch();

        //in minimal catalogue we have one root category
        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('category_trees', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfCategories
     * @param int $numberOfChilds
     */
    private function createCategoriesWithChild(int $numberOfCategories, int $numberOfChilds): void
    {
        $i = 0;
        while ($i < $numberOfCategories) {
            $categoryRoot = $this->createCategory([
                'code' => 'new_category_' . rand()
            ]);

            $j = 0;
            while ($j < $numberOfChilds) {
                $this->createCategory([
                    'code' => 'new_child_category_' . rand(),
                    'parent' => $categoryRoot->getCode()
                ]);
                $j++;
            }
            $i++;
        }
    }
}
