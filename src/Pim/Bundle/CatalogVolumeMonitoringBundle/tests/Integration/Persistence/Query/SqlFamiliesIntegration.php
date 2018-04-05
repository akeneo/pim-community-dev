<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;

class SqlFamiliesIntegration extends BuilderQueryTestCase
{
    public function testGetCountOfFamilies()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.families');
        $this->createFamilies(4);

        $volume = $query->fetch();

        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('families', $volume->getVolumeName());
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
