<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetWarningCount;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\StoppableJobInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionResultRecordHydrator implements HydratorInterface
{
    private JobRegistry $registry;
    private GetJobExecutionTracking $getJobExecutionTracking;
    /** @TODO pull up to 6.0 remove this line */
    private ?GetWarningCount $getWarningCount;

    public function __construct(
        JobRegistry $registry,
        GetJobExecutionTracking $getJobExecutionTracking,
        /** @TODO pull up to 6.0 remove this line */
        GetWarningCount $getWarningCount = null
    ) {
        $this->registry = $registry;
        $this->getJobExecutionTracking = $getJobExecutionTracking;
        /** @TODO pull up to 6.0 remove this line */
        $this->getWarningCount = $getWarningCount;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $records = [];
        foreach ($qb->getQuery()->execute() as $record) {
            $job = $this->registry->get($record['jobName']);
            $recordStatus = new BatchStatus($record['status']);
            $isStoppable = $recordStatus->isRunning() && $job instanceof StoppableJobInterface && $job->isStoppable();

            $jobExecutionTracking = $this->getJobExecutionTracking->execute($record['id']);
            $records[] = new ResultRecord(
                array_merge(
                    $record,
                    [
                        /** @TODO pull up to 6.0 remove this line */
                        'warningCount' => $this->getWarningCount ? $this->getWarningCount->execute($record['id']) : 0,
                        'isStoppable'     => $isStoppable,
                        'currentStep'     => $jobExecutionTracking->currentStep,
                        'totalSteps'       => $jobExecutionTracking->totalSteps,
                        'hasError'        => $jobExecutionTracking->hasError(),
                        'hasWarning' => $jobExecutionTracking->hasWarning()
                    ]
                )
            );
        }

        return $records;
    }
}
