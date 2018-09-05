<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class CountFamiliesIntegration extends QueryTestCase
{
    public function testGetCountOfFamilies()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_families');
        $this->createFamilies(4);

        $volume = $query->fetch();

        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('count_families', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfFamilies
     */
    private function createFamilies(int $numberOfFamilies): void
    {
        $i = 0;
        while ($i < $numberOfFamilies) {
            $this->createFamily([
                'code' => 'new_family_' . rand()
            ]);
            $i++;
        }
    }
}
