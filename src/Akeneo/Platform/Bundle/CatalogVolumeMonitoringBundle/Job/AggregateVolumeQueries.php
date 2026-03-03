<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Job;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Service\VolumeAggregation;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Aggregate the result of all the volume queries that should not be executed live.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AggregateVolumeQueries implements TaskletInterface
{
    protected const JOB_CODE = 'aggregate_volume_queries';

    protected StepExecution $stepExecution;

    public function __construct(private VolumeAggregation $volumeAggregation)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $this->volumeAggregation->aggregate();
    }
}
