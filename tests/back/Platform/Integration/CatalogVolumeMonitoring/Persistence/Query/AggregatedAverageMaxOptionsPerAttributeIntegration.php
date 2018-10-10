<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;

class AggregatedAverageMaxOptionsPerAttributeIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testGetAverageAndMaxNumberOfProductValuesPerProduct()
    {
        $repository = $this->get('pim_volume_monitoring.volume.repository.aggregated_volume');
        $repository->add(new AggregatedVolume(
                'average_max_options_per_attribute',
                ['value' => [
                    'max' => 23,
                    'average' => 15
                ]],
                new \DateTime())
        );

        $query = $this->get('pim_volume_monitoring.persistence.query.aggregated_average_max_options_per_attribute');
        $volume = $query->fetch();

        $this->assertEquals('average_max_options_per_attribute', $volume->getVolumeName());
        $this->assertEquals(23, $volume->getMaxVolume());
        $this->assertEquals(15, $volume->getAverageVolume());
        $this->assertFalse($volume->hasWarning());
    }
}
