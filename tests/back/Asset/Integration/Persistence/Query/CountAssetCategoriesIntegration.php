<?php

declare(strict_types=1);

namespace AkeneoTest\Asset\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Asset\Integration\Persistence\PimEnterpriseQueryTestCase;

class CountAssetCategoriesIntegration extends PimEnterpriseQueryTestCase
{
    /**
     * @throws \Exception
     */
    public function testGetCountOfAssetCategories()
    {
        $query = $this->get('pimee_volume_monitoring.persistence.query.count_asset_categories');
        $this->createAssetCategories(8);

        $volume = $query->fetch();

        //in minimal catalog, it already have one asset_category
        Assert::assertEquals(9, $volume->getVolume());
        Assert::assertEquals('count_asset_categories', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAssetCategories
     * @throws \Exception
     */
    private function createAssetCategories(int $numberOfAssetCategories): void
    {
        $i = 0;
        while ($i < $numberOfAssetCategories) {
            $this->createAssetCategory([
                'code' => 'new_asset_category_' . rand(),
                'parent' => 'asset_main_catalog'
            ]);
            $i++;
        }
    }
}
