<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxProductModelValuesIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfProductModelValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_product_model_values');
        $this->createProductModelWithProductValues(4);
        $this->createProductModelWithProductValues(6);

        $volume = $query->fetch();

        $this->assertEquals(6, $volume->getMaxVolume());
        $this->assertEquals(5, $volume->getAverageVolume());
        $this->assertEquals('average_max_product_model_values', $volume->getVolumeName());
        $this->assertFalse($volume->hasWarning());
    }

    public function testGetAverageAndMaximumNumberOfProductModelValuesDoesNotTakeAccountOfProductValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_product_model_values');
        $this->createProductModelWithProductValues(4);
        $this->createProductModelWithProductValues(6);
        $this->createProductWithProductValues(8);

        $volume = $query->fetch();

       $this->assertEquals(6, $volume->getMaxVolume());
       $this->assertEquals(5, $volume->getAverageVolume());
       $this->assertEquals('average_max_product_model_values', $volume->getVolumeName());
       $this->assertFalse($volume->hasWarning());
    }
}
