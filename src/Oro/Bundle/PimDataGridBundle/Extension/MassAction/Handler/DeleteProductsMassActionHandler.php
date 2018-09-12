<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductEvents;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Mass delete products action handler
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteProductsMassActionHandler extends DeleteMassActionHandler
{
    private const MAX_LIMIT = 1000;

    /** @var IndexerInterface */
    private $indexRemover;

    /** @var CursorFactoryInterface */
    private $cursorFactory;

    public function __construct(
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        BulkRemoverInterface $indexRemover,
        CursorFactoryInterface $cursorFactory
    ) {
        parent::__construct($hydrator, $translator, $eventDispatcher);

        $this->indexRemover = $indexRemover;
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * {@inheritdoc}
     *
     * Dispatch two more events for products (PRE_MASS_REMOVE and POST_MASS_REMOVE)
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_DELETE_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        $pqb = $datasource->getProductQueryBuilder();
        $cursor = $this->cursorFactory->createCursor($pqb->getQueryBuilder()->getQuery());

        $selectedItemsCount =  $cursor->count();
        if (static::MAX_LIMIT < $selectedItemsCount) {
            return new MassActionResponse(false, $this->translator->trans(
                'oro.grid.mass_action.delete.item_limit',
                ['%count%' => $selectedItemsCount, '%limit%' => $this::MAX_LIMIT]
            ));
        }

        $objectIds = $this->filterProducts($cursor);

        try {
            $this->eventDispatcher->dispatch(ProductEvents::PRE_MASS_REMOVE, new GenericEvent($objectIds));

            $countRemoved = $datasource->getMassActionRepository()->deleteFromIds($objectIds);

            $this->indexRemover->removeAll($objectIds);

            $this->eventDispatcher->dispatch(ProductEvents::POST_MASS_REMOVE, new GenericEvent($objectIds));
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
     * Only returns the product id's within a list of product and product models records.
     *
     * TODO: PIM-6357 - Scenario should be removed once mass edits work for product models
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    private function filterProducts(CursorInterface $cursor): array
    {
        $objectIds = [];
        foreach ($cursor as $productObject) {
            if ($productObject instanceof ProductInterface) {
                $objectIds[] = $productObject->getId();
            }
        }

        return $objectIds;
    }
}
