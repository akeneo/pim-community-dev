<?php
declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Akeneo\Platform\Bundle\ImportExportBundle\Registry\NotVisibleJobsRegistry;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

/**
 * Black list job codes from process tracker grid.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BlackListJobsFromGridListener
{
    /** @var NotVisibleJobsRegistry */
    private $notVisibleJobs;

    public function __construct(NotVisibleJobsRegistry $notVisibleJobsRegistry)
    {
        $this->notVisibleJobs = $notVisibleJobsRegistry;
    }

    public function onBuildAfter(BuildAfter $event): void
    {
        $dataSource = $event->getDatagrid()->getDatasource();

        $parameters = $dataSource->getParameters();
        $parameters['blackListedJobCodes'] = $this->notVisibleJobs->getCodes();
        $dataSource->setParameters($parameters);

        $qb = $dataSource->getQueryBuilder();
        $qb->andWhere($qb->expr()->notIn('j.code', ':blackListedJobCodes'));
    }
}
