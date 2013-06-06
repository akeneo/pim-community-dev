<?php

namespace Oro\Bundle\SearchBundle\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Parser;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Engine\AbstractEngine;

class Indexer
{
    const TEXT_ALL_DATA_FIELD   = 'all_text';

    const RELATION_ONE_TO_ONE   = 'one-to-one';
    const RELATION_MANY_TO_MANY = 'many-to-many';
    const RELATION_MANY_TO_ONE  = 'many-to-one';
    const RELATION_ONE_TO_MANY  = 'one-to-many';

    /**
     * @var AbstractEngine
     */
    protected $adapter;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     *
     * @param ObjectManager  $em
     * @param AbstractEngine $adapter
     * @param ObjectMapper   $mapper
     */
    public function __construct(ObjectManager $em, AbstractEngine $adapter, ObjectMapper $mapper)
    {
        $this->em      = $em;
        $this->adapter = $adapter;
        $this->mapper  = $mapper;
    }

    /**
     * Get array with entities aliases and labels
     *
     * @return array
     */
    public function getEntitiesLabels()
    {
        return $this->mapper->getEntitiesLabels();
    }

    /**
     * @param  string  $searchString
     * @param  integer $offset
     * @param  integer $maxResults
     * @param  string  $from
     * @param  integer $page
     * @return Result
     */
    public function simpleSearch($searchString, $offset = 0, $maxResults = 0, $from = null, $page = 0)
    {
        $query = $this->select();

        if ($from) {
            $query->from($from);
        } else {
            $query->from('*');
        }

        $query->andWhere(self::TEXT_ALL_DATA_FIELD, '~', $searchString, 'text');

        if ($maxResults > 0) {
            $query->setMaxResults($maxResults);
        } else {
            $query->setMaxResults(Query::INFINITY);
        }

        if ($page > 0) {
            $query->setFirstResult($maxResults * ($page - 1));
        } elseif ($offset > 0) {
            $query->setFirstResult($offset);
        }

        return $this->query($query);
    }

    /**
     * Get query builder with select instance
     *
     * @return Query
     */
    public function select()
    {
        $query = new Query(Query::SELECT);

        $query->setMappingConfig($this->mapper->getMappingConfig());
        $query->setEntityManager($this->em);

        return $query;
    }

    /**
     * Run query with query builder
     *
     * @param  Query  $query
     * @return Result
     */
    public function query(Query $query)
    {
        return $this->adapter->search($query);
    }

    /**
     * Advanced search from API
     *
     * @param  string $searchString
     * @return Result
     */
    public function advancedSearch($searchString)
    {
        $parser = new Parser($this->mapper->getMappingConfig());

        return $this->query($parser->getQueryFromString($searchString));
    }
}
