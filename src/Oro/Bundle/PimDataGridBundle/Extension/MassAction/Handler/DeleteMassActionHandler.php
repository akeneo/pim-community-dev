<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Mass delete action handler
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $responseMessage = 'pim_datagrid.mass_action.delete.success_message';

    /**
     * Constructor
     *
     * @param HydratorInterface        $hydrator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->hydrator = $hydrator;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_DELETE_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        // hydrator uses index by id
        $objectIds = $datasource->getResults();

        try {
            $countRemoved = $datasource->getMassActionRepository()->deleteFromIds($objectIds);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return new MassActionResponse(false, $this->translator->trans($errorMessage));
        }

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $objectIds);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_DELETE_POST_HANDLER, $massActionEvent);

        return $this->getResponse($massAction, $countRemoved);
    }

    /**
     * Prepare mass action response
     *
     * @param MassActionInterface $massAction
     * @param int                 $countRemoved
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionInterface $massAction, $countRemoved = 0)
    {
        $responseMessage = $massAction->getOptions()->offsetGetByPath(
            '[messages][success]',
            $this->responseMessage
        );

        return new MassActionResponse(
            true,
            $this->translator->trans($responseMessage),
            ['count' => $countRemoved]
        );
    }
}
