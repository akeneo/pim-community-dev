<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension;

/**
 * A listener to add JobCode for "job instance show" pages
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddJobCodeToGridListener
{
    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Add JobCode parameter to the current query builder
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $jobCode = $this->requestParams->get('jobCode');
        $dataSource = $event->getDatagrid()->getDatasource();

        $parameters = $dataSource->getParameters();
        $parameters['jobCode'] = $jobCode;
        $dataSource->setParameters($parameters);

        $qb = $dataSource->getQueryBuilder();
        $qb->andWhere($qb->expr()->eq('j.code', ':jobCode'));
        $qb->orderBy('e.startTime', OrmSorterExtension::DIRECTION_DESC);
    }
}
