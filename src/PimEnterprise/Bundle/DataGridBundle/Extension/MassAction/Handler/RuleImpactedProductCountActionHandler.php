<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mass calculation of count of impacted products by rules
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleImpactedProductCountActionHandler implements MassActionHandlerInterface
{
    /** @var HydratorInterface */
    protected $hydrator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param HydratorInterface        $hydrator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(HydratorInterface $hydrator, EventDispatcherInterface $eventDispatcher)
    {
        $this->hydrator        = $hydrator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        $results = $datasource->getResults();

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $results);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_POST_HANDLER, $massActionEvent);

        return $results;
    }
}
