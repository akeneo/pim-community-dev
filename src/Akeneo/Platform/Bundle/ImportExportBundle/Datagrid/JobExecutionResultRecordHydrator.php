<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

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

    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
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

            $records[] = new ResultRecord(array_merge(
                $record,
                //TODO replace with $isStoppable
                ['isStoppable' => true]
            ));
        }

        return $records;
    }
}
