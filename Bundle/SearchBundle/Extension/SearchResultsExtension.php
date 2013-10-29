<?php
namespace Oro\Bundle\SearchBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecord;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class SearchResultsExtension extends AbstractExtension
{
    const TYPE_PATH  = '[columns][entity][type]';
    const TYPE_VALUE = 'search-result';

    /** @var ResultFormatter */
    protected $resultFormatter;

    /**
     * @param RequestParameters $requestParams
     * @param ResultFormatter   $formatter
     */
    public function __construct(RequestParameters $requestParams, ResultFormatter $formatter)
    {
        parent::__construct($requestParams);

        $this->resultFormatter = $formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(self::TYPE_PATH) == self::TYPE_VALUE ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
        $rows    = $result->offsetGetByPath('[data]');
        $resultItems = $this->resultFormatter->getResultEntities($rows);

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
                'entity'       => $entity,
            );

//            $resultRecord = [];
//            $record       = new ResultRecord($row);
//
//            foreach ($toProcess as $name => $config) {
//                $property            = $this->getPropertyObject($name, $config);
//                $resultRecord[$name] = $property->getValue($record);
//            }
        }

        // set results
        $result->offsetSet('data', $resultRows);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 10;
    }
}
