<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountCategoriesIntegration extends QueryTestCase
{
    public function testGetCountOfCategories()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_categories');
        $this->createCategories(8);

        $volume = $query->fetch();

        //in minimal catalog we have one category
        Assert::assertEquals(9, $volume->getVolume());
        Assert::assertEquals('count_categories', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfCategories
     */
    private function createCategories(int $numberOfCategories): void
    {
        $i = 0;
        while ($i < $numberOfCategories) {
            $this->createCategory([
                'code' => 'new_category_' . rand()
            ]);
            $i++;
        }
    }
}
