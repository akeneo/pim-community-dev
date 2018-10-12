<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountProductValuesIntegration extends QueryTestCase
{
    public function testGetCountOfProductValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_product_values');
        $this->createProductWithProductValues(4);
        $this->createProductWithProductValues(6);

        $volume = $query->fetch();

        Assert::assertEquals(10, $volume->getVolume());
        Assert::assertEquals('count_product_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    public function testGetCountOfProductValuesDoesNotCountProductModelValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_product_values');
        $this->createProductWithProductValues(4);
        $this->createProductWithProductValues(6);
        $this->createProductModelWithProductValues(2);

        $volume = $query->fetch();

        Assert::assertEquals(10, $volume->getVolume());
        Assert::assertEquals('count_product_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
