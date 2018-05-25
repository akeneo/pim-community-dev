<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
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

    /** @var CursorFactoryInterface */
    protected $cursorFactory;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CursorFactoryInterface   $cursorFactory
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, CursorFactoryInterface $cursorFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cursorFactory = $cursorFactory;
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

        $pqb = $datasource->getProductQueryBuilder();
        $cursor = $this->cursorFactory->createCursor($pqb->getQueryBuilder()->getQuery());

        $objectIds = [];
        foreach ($cursor as $productObject) {
            if ($productObject instanceof EntityWithValuesDraftInterface) {
                $objectIds[] = $productObject->getId();
            }
        }

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $objectIds);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_REFUSE_POST_HANDLER, $massActionEvent);

        return $objectIds;
    }
}
