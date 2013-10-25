<?php
namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class SearchResultsExtension extends AbstractExtension
{
    const TYPE_PATH = '[columns][entity][type]';
    const TYPE_VALUE = 'search-result';

    /** @var ResultFormatter */
    protected $resultFormatter;

    /**
     * @param RequestParameters $requestParams
     * @param ResultFormatter $formatter
     */
    public function __construct(RequestParameters $requestParams, ResultFormatter $formatter)
    {
        parent::__construct($requestParams);

        $this->resultFormatter = $formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        return $this->accessor->getValue($config, self::TYPE_PATH) == self::TYPE_VALUE ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        $rows       = (array)$result->data;
        $results    = [];

        $this->resultFormatter->getResultEntities($result);

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


            $resultRecord = [];
            $record       = new ResultRecord($row);

            foreach ($toProcess as $name => $config) {
                $property            = $this->getPropertyObject($name, $config);
                $resultRecord[$name] = $property->getValue($record);
            }

            $results[] = $resultRecord;
        }

        $result->data = $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 10;
    }
}
