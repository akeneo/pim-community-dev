<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use PimEnterprise\Bundle\ProductAssetBundle\tests\Integration\Persistence\PimEnterpriseBuilderQueryTestCase;

class CountAssetsIntegration extends PimEnterpriseBuilderQueryTestCase
{
    /**
     * @throws \Exception
     */
    public function testGetCountOfAssets()
    {
        $query = $this->get('pimee_volume_monitoring.persistence.query.count_assets');
        $this->createAssets(8);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getVolume());
        Assert::assertEquals('count_assets', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfAssets
     * @throws \Exception
     */
    private function createAssets(int $numberOfAssets): void
    {
        $i = 0;
        while ($i < $numberOfAssets) {
            $this->createAsset([
                'code' => 'new_asset_' . rand()
            ]);
            $i++;
        }
    }
}
