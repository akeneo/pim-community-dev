<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Entity\Query as QueryLog;

/**
 * Connector abstract class
 */
abstract class AbstractEngine
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $logQueries;

    /**
     * Init entity manager
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param bool                        $logQueries
     */
    public function __construct(EntityManager $em, $logQueries)
    {
        $this->em = $em;
        $this->logQueries = $logQueries;
    }

    /**
     * Insert or update record
     *
     * @param object $entity
     * @param bool   $realtime
     * @param bool   $needToCompute
     *
     * @return mixed
     */
    abstract public function save($entity, $realtime = true, $needToCompute = false);

    /**
     * Insert or update record
     *
     * @param object $entity
     * @param bool   $realtime
     *
     * @return mixed
     */
    abstract public function delete($entity, $realtime = true);

    /**
     * Reload search index
     *
     * @return int Count of index records
     */
    abstract public function reindex();

    /**
     * Search query with query builder
     * Must return array
     * array(
     *   'results' - array of Oro\Bundle\SearchBundle\Query\Result\Item objects
     *   'records_count' - count of records without limit parameters in query
     * )
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return array
     */
    abstract protected function doSearch(Query $query);

    /**
     * Search query with query builder
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function search(Query $query)
    {
        $searchResult = $this->doSearch($query);
        $result = new Result($query, $searchResult['results'], $searchResult['records_count']);

        if ($this->logQueries) {
            $this->logQuery($result);
        }

        return $result;
    }

    /**
     * Log query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Result $result
     */
    protected function logQuery(Result $result)
    {
        $logRecord = new QueryLog;
        $logRecord->setEntity(serialize($result->getQuery()->getFrom()));
        $logRecord->setQuery(serialize($result->getQuery()->getOptions()));
        $logRecord->setResultCount($result->count());

        $this->em->persist($logRecord);
        $this->em->flush();
    }
}
