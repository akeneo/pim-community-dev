<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\Orm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionResultRecordHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $records = [];
        foreach ($qb->getQuery()->execute() as $record) {
            $records[] = new ResultRecord(array_merge($record, ['nice' => 'yolo']));
        }

        return $records;
    }
}
