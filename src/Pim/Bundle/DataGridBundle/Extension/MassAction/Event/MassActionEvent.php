<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

/**
 * Mass action event allows to do add easily some extra code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionEvent extends Event
{
    /**
     * @var DatagridInterface $datagrid
     */
    protected $datagrid;

    /**
     * @var MassActionInterface $massAction
     */
    protected $massAction;

    /**
     * Constructor
     *
     * @param DatagridInterface $datagrid
     */
    public function __construct(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $this->datagrid   = $datagrid;
        $this->massAction = $massAction;
    }

    /**
     * Get datagrid
     *
     * @return DatagridInterface
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }

    /**
     * Get mass action
     *
     * @return MassActionInterface
     */
    public function getMassAction()
    {
        return $this->massAction;
    }
}
