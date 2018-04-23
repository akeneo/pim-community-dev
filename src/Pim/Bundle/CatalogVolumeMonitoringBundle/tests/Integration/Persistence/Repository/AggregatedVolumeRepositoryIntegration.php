<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Repository;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

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

        $query = $this->get('pim_volume_monitoring.persistence.query.aggregated_count_product_values');
        $volume = $query->fetch();

        $this->assertEquals('count_product_values', $volume->getVolumeName());
        $this->assertEquals(47, $volume->getVolume());
    }
}
