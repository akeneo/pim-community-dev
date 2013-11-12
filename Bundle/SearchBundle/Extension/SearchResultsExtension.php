<?php
namespace Oro\Bundle\SearchBundle\Extension;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class SearchResultsExtension extends AbstractExtension
{
    const TYPE_PATH  = '[columns][entity][type]';
    const TYPE_VALUE = 'search-result';

    /** @var ResultFormatter */
    protected $resultFormatter;

    /** @var EntityManager */
    protected $em;

    /** @var ObjectMapper */
    protected $mapper;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param RequestParameters        $requestParams
     * @param ResultFormatter          $formatter
     * @param EntityManager            $em
     * @param ObjectMapper             $mapper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestParameters $requestParams,
        ResultFormatter $formatter,
        EntityManager $em,
        ObjectMapper $mapper,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($requestParams);

        $this->resultFormatter = $formatter;
        $this->em              = $em;
        $this->mapper          = $mapper;
        $this->dispatcher      = $dispatcher;
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
        $rows       = $result->offsetGetByPath('[data]');
        $rows       = array_map(
            function (ResultRecordInterface $record) {
                return $record->getRootEntity();
            },
            $rows
        );
        $entities   = $this->resultFormatter->getResultEntities($rows);

        $resultRows = [];
        foreach ($rows as $row) {
            $entity     = null;
            $entityName = $row->getEntityName();
            $entityId   = $row->getRecordId();
            if (isset($entities[$entityName][$entityId])) {
                $entity = $entities[$entityName][$entityId];
            }

            $item         = new ResultItem(
                $this->em,
                $entityName,
                $entityId,
                null,
                null,
                null,
                $this->mapper->getEntityConfig($entityName)
            );
            $resultRows[] = new ResultRecord(['entity' => $entity, 'indexer_item' => $item]);

            $this->dispatcher->dispatch(PrepareResultItemEvent::EVENT_NAME, new PrepareResultItemEvent($item, $entity));
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
