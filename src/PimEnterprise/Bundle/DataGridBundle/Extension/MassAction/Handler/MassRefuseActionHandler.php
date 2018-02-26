<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mass refuse action handler
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class MassRefuseActionHandler implements MassActionHandlerInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_REFUSE_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $products = $datasource->getProductQueryBuilder()->execute();

        $objectIds = [];
        foreach ($products as $productObject) {
            if ($productObject instanceof ProductDraftInterface) {
                $objectIds[] = $productObject->getId();
            }
        }

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $objectIds);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_REFUSE_POST_HANDLER, $massActionEvent);

        return $objectIds;
    }
}
