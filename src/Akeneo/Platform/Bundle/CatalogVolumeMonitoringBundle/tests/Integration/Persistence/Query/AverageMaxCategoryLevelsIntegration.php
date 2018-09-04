<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxCategoryLevelsIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfCategoryLevels()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_category_levels');
        $this->createCategoryWithLevel(4);
        $this->createCategoryWithLevel(6);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getMaxVolume());
        Assert::assertEquals(3, $volume->getAverageVolume());
        Assert::assertEquals('average_max_category_levels', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
