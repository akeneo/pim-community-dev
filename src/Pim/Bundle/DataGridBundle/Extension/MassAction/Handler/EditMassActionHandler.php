<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;

/**
 * Handler for mass edit action
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditMassActionHandler implements MassActionHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        return $datagrid->getDatasource()->getQueryBuilder();
    }
}
