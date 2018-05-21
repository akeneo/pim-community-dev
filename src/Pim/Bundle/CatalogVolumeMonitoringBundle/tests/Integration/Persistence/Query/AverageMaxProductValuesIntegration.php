<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class AverageMaxProductValuesIntegration extends QueryTestCase
{
    public function testGetAverageAndMaximumNumberOfProductValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_product_values');
        $this->createProductWithProductValues(4);
        $this->createProductWithProductValues(6);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_product_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    public function testGetAverageAndMaximumNumberOfProductValuesDoesNotTakeAccountOfProductModelValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.average_max_product_values');
        $this->createProductWithProductValues(4);
        $this->createProductWithProductValues(6);
        $this->createProductModelWithProductValues(8);

        $volume = $query->fetch();

        Assert::assertEquals(6, $volume->getMaxVolume());
        Assert::assertEquals(5, $volume->getAverageVolume());
        Assert::assertEquals('average_max_product_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
