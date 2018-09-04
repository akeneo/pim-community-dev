<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;

class CountProductsIntegration extends QueryTestCase
{
    public function testGetCountOfProducts()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_products');
        $this->createProducts(8);

        $volume = $query->fetch();

        Assert::assertEquals(8, $volume->getVolume());
        Assert::assertEquals('count_products', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfProducts
     */
    protected function createProducts(int $numberOfProducts) : void
    {
        $i = 0;

        while ($i < $numberOfProducts) {
            $this->createProduct([
                'identifier' => 'new_product_'.rand()
            ]);
            $i++;
        }
    }
}
