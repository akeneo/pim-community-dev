<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

/**
 * Handler interface for mass actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassActionHandlerInterface
{
    /**
     * Handle mass action
     *
     * @param DatagridInterface   $datagrid
     * @param MassActionInterface $massAction
     *
     * @return mixed
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction);
}
