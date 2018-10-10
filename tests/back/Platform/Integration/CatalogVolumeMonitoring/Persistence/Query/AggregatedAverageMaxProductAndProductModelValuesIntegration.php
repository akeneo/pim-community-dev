<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

class AggregatedAverageMaxProductAndProductModelValuesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testGetAverageAndMaxNumberOfProductsAndProductModelsValues()
    {
        $repository = $this->get('pim_volume_monitoring.volume.repository.aggregated_volume');

        $repository->add(new AggregatedVolume('count_products', ['value' => 21], new \DateTime()));
        $repository->add(new AggregatedVolume('count_product_models', ['value' => 13], new \DateTime()));
        $repository->add(new AggregatedVolume('count_product_values', ['value' => 315], new \DateTime()));
        $repository->add(new AggregatedVolume('count_product_model_values', ['value' => 143], new \DateTime()));

        $repository->add(new AggregatedVolume(
            'average_max_product_values',
            ['value' => [
                'max' => 23,
                'average' => 15
            ]],
            new \DateTime())
        );
        $repository->add(new AggregatedVolume(
            'average_max_product_model_values',
            ['value' => [
                'max' => 17,
                'average' => 11
            ]],
            new \DateTime())
        );

        $query = $this->get('pim_volume_monitoring.persistence.query.aggregated_average_max_product_and_product_model_values');
        $volume = $query->fetch();

        $this->assertEquals('average_max_product_and_product_model_values', $volume->getVolumeName());
        $this->assertEquals(23, $volume->getMaxVolume());
        $this->assertEquals(14, $volume->getAverageVolume());
        $this->assertFalse($volume->hasWarning());
    }
}
