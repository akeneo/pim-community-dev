<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;

use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

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
     * @param ResultFormatter $resultFormatter
     * @param string $datagridName
     */
    public function __construct(ResultFormatter $resultFormatter, $datagridName)
    {
        $this->resultFormatter = $resultFormatter;
        $this->datagridName    = $datagridName;
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

            $resultRows[] = array(
                'indexer_item' => $row,
                'entity' => $entity,
            );
        }

        $event->setRows($resultRows);
    }
}
