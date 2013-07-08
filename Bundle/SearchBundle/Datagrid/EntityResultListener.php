<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class EntityResultListener
{
    /**
     * @var string
     */
    protected $datagridName;

    /**
     * @var ResultFormatter
     */
    protected $resultFormatter;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param ResultFormatter $resultFormatter
     * @param string $datagridName
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function __construct(ResultFormatter $resultFormatter, $datagridName, EventDispatcher $dispatcher)
    {
        $this->resultFormatter = $resultFormatter;
        $this->datagridName    = $datagridName;
        $this->dispatcher      = $dispatcher;
    }

    /**
     * @param ResultDatagridEvent $event
     */
    public function processResult(ResultDatagridEvent $event)
    {
        if (!$event->isDatagridName($this->datagridName)) {
            return;
        }

        /** @var $rows Item[] */
        $rows = $event->getRows();
        $entities = $this->resultFormatter->getResultEntities($rows);

        // add entities to result rows
        $resultRows = array();
        foreach ($rows as $row) {
            $entity     = null;
            $entityName = $row->getEntityName();
            $entityId   = $row->getRecordId();
            if (isset($entities[$entityName][$entityId])) {
                $entity = $entities[$entityName][$entityId];
            }

            $this->dispatcher->dispatch(PrepareResultItemEvent::EVENT_NAME, new PrepareResultItemEvent($row, $entity));

            $resultRows[] = array(
                'indexer_item' => $row,
                'entity' => $entity,
            );
        }

        $event->setRows($resultRows);
    }
}
