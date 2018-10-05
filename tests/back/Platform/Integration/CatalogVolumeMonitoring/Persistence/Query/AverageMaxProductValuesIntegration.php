<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;
use PHPUnit\Framework\Assert;


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
