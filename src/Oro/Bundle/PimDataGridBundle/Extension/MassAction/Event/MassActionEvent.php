<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Symfony\Component\EventDispatcher\Event;

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
     * @var DatagridInterface
     */
    protected $datagrid;

    /**
     * @var MassActionInterface
     */
    protected $massAction;

    /**
     * Entities or documents which are impacted by mass action
     * Can be just ids if needed
     *
     * @var array
     */
    protected $objects;

    /**
     * Constructor
     *
     * @param DatagridInterface   $datagrid
     * @param MassActionInterface $massAction
     * @param array               $objects
     */
    public function __construct(DatagridInterface $datagrid, MassActionInterface $massAction, array $objects)
    {
        $this->datagrid = $datagrid;
        $this->massAction = $massAction;
        $this->objects = $objects;
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

    /**
     * Get objects
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
