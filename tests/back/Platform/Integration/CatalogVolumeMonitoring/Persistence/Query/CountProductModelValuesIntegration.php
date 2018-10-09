<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountProductModelValuesIntegration extends QueryTestCase
{
    public function testGetCountOfProductModelValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_product_model_values');
        $this->createProductModelWithProductValues(4);
        $this->createProductModelWithProductValues(6);

        $volume = $query->fetch();

        Assert::assertEquals(10, $volume->getVolume());
        Assert::assertEquals('count_product_model_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    public function testGetCountOfProductValuesDoesNotCountProductValues()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_product_model_values');
        $this->createProductModelWithProductValues(4);
        $this->createProductModelWithProductValues(6);
        $this->createProductWithProductValues(4);

        $volume = $query->fetch();

        Assert::assertEquals(10, $volume->getVolume());
        Assert::assertEquals('count_product_model_values', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
