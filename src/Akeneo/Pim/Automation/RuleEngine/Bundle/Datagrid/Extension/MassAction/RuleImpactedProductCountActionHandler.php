<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\MassAction;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\MassActionEvents;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

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
        $this->hydrator = $hydrator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch($massActionEvent, MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_PRE_HANDLER);

        $datasource = $datagrid->getDatasource();
        Assert::implementsInterface($datasource, DatasourceInterface::class);
        $datasource->setHydrator($this->hydrator);

        $results = $datasource->getResults();

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $results);
        $this->eventDispatcher->dispatch($massActionEvent, MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_POST_HANDLER);

        return $results;
    }
}
