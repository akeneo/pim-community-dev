<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sequential edit action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditActionHandler implements MassActionHandlerInterface
{
    /** @var HydratorInterface */
    protected $hydrator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param HydratorInterface        $hydrator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(HydratorInterface $hydrator, EventDispatcherInterface $eventDispatcher)
    {
        $this->hydrator = $hydrator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::SEQUENTIAL_EDIT_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        $results = $this->getResultsFromDatasource($datasource);

        $massActionEvent = new MassActionEvent($datagrid, $massAction, $results);
        $this->eventDispatcher->dispatch(MassActionEvents::SEQUENTIAL_EDIT_POST_HANDLER, $massActionEvent);

        return $results;
    }

    /**
     * @param DatasourceInterface $datasource
     *
     * @return array
     */
    public function getResultsFromDatasource(DatasourceInterface $datasource)
    {
        $results = $datasource->getResults();

        if (!isset($results['data'])) {
            throw new \LogicException('Datasource results must contain at least one result, none given.');
        }

        $productIds = [];
        foreach ($results['data'] as $result) {
            if (!$result instanceof ResultRecordInterface) {
                throw InvalidObjectException::objectExpected($result, ResultRecordInterface::class);
            }

            $productIds[] = $result->getValue('id');
        }

        return $productIds;
    }
}
