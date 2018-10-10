<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

class AggregatedCountProductAndProductModelValuesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testGetCountOfProductAndProductModelValuesFromAggregatedVolumes()
    {
        $repository = $this->get('pim_volume_monitoring.volume.repository.aggregated_volume');
        $repository->add(new AggregatedVolume('count_product_values', ['value' => 42], new \DateTime()));
        $repository->add(new AggregatedVolume('count_product_model_values', ['value' => 16], new \DateTime()));

        $query = $this->get('pim_volume_monitoring.persistence.query.aggregated_count_product_and_product_model_values');
        $volume = $query->fetch();

        $this->assertEquals('count_product_and_product_model_values', $volume->getVolumeName());
        $this->assertEquals(58, $volume->getVolume());
        $this->assertFalse($volume->hasWarning());
    }
}
