<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;

class SqlProductsIntegration extends BuilderQueryTestCase
{
    public function testGetCountOfProducts()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.products');
        $this->createProducts(8);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getVolume());
        Assert::assertEquals('products', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }
}
