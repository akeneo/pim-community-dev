<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Repository;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

class AggregatedVolumeRepositoryIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testAddAnAggregatedVolume(): void
    {
        $repository = $this->get('pim_volume_monitoring.volume.repository.aggregated_volume');
        $repository->add(new AggregatedVolume('count_product_values', ['value' => 47], new \DateTime()));

        $query = $this->get('pim_volume_monitoring.persistence.query.aggregated_count_product_and_product_model_values');
        $volume = $query->fetch();

        $this->assertEquals('count_product_and_product_model_values', $volume->getVolumeName());
        $this->assertEquals(47, $volume->getVolume());
    }
}
