<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;

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
     * @var HydratorInterface $hydrator
     */
    protected $hydrator;

    /**
     * @var EventDispatcher $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param HydratorInterface        $hydrator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        HydratorInterface $hydrator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->hydrator        = $hydrator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, array());
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_EDIT_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        // hydrator uses index by id
        $objectIds = $datasource->getResults();

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, array());
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_EDIT_POST_HANDLER, $massActionEvent);

        return $objectIds;
    }
}
