<?php


namespace Akeneo\ReferenceEntity\Integration\PublicApi\Analytics;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AggregatedAverageMaxNumberOfValuesPerRecord;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregatedAverageMaxNumberOfValuesPerRecordTest extends SqlIntegrationTestCase
{
    private AggregatedAverageMaxNumberOfValuesPerRecord $aggregatedAverageMaxNumberOfValuePerRecord;

    public function setUp(): void
    {
        parent::setUp();
        $this->aggregatedAverageMaxNumberOfValuePerRecord = $this->get('akeneo.reference_entity.infrastructure.public_api.analytics.aggregated_average_max_number_of_values_per_record');
        $this->fixturesLoader->load();
        $this->get('pim_volume_monitoring.volume.aggregation')->aggregate();
    }

    /**
     * @test
     */
    public function it_returns_the_average_max_number_of_value_per_record()
    {
        $averageMaxVolume = $this->aggregatedAverageMaxNumberOfValuePerRecord->fetch();
        Assert::assertEquals(0, $averageMaxVolume->getAverageVolume());
        Assert::assertEquals(0, $averageMaxVolume->getMaxVolume());
        Assert::assertEquals('average_max_number_of_values_per_record', $averageMaxVolume->getVolumeName());
    }
}
