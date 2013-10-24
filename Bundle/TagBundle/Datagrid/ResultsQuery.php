<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TagBundle\Entity\Tagging;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Query\Result\Item as ResultItem;

class ResultsQuery extends Query
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * {@inheritdoc}
     */
    public function __construct($queryBuilder, EntityManager $em, ObjectMapper $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;

        parent::__construct($em);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        $originResults = parent::execute($params, $hydrationMode);

        $results = array();
        /** @var Tagging $item */
        foreach ($originResults as $item) {
            $results[] = new ResultItem(
                $this->em,
                $item->getEntityName(),
                $item->getRecordId(),
                null,
                null,
                null,
                $this->mapper->getEntityConfig($item->getEntityName())
            );
        }

        return $results;
    }
}
