<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class CountUseableAsGridFilterAttributesIntegration extends QueryTestCase
{
    public function testGetCountOfScopableAttributes()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_useable_as_grid_filter_attributes');
        $this->createUseableAsGridFilterAttributes(5);

        $volume = $query->fetch();

        Assert::assertEquals(5, $volume->getVolume());
        Assert::assertEquals('count_useable_as_grid_filter_attributes', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
