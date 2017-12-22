<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\ProductEvents;
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
    const MAX_LIMIT = 1000;

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
        $cursor = $this->cursorFactory->createCursor($pqb->getQueryBuilder()->getQuery(), ['from' => 0]);

        $selectedItemsCount =  $cursor->count();
        if ($this::MAX_LIMIT < $selectedItemsCount) {
            return new MassActionResponse(false, $this->translator->trans(
                'oro.grid.mass_action.delete.item_limit',
                ['%count%' => $selectedItemsCount, '%limit%' => $this::MAX_LIMIT]
            ));
        }

        while ($cursor->valid()) {
            $objectIds = [];
            foreach ($cursor as $productObject) {
                $objectIds[] = $productObject->getId();
            }
            $cursor->next();
        }

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
     * Only returns the product records within a list of product and product models records.
     *
     * TODO: PIM-6357 - Scenario should be removed once mass edits work for product models
     *
     * @param array $resultRecords
     *
     * @return array
     */
    private function filterProductRecords(array $resultRecords): array
    {
        $productRecords = [];
        foreach ($resultRecords['data'] as $resultRecord) {
            /** @var ResultRecord $resultRecord */
            if ($resultRecord->getValue('document_type') === IdEncoder::PRODUCT_TYPE) {
                $productRecords[] = $resultRecord;
            }
        }
        $resultRecords['data'] = $productRecords;

        return $resultRecords;
    }
}
