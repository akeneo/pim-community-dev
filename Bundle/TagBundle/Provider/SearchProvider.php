<?php

namespace Oro\Bundle\TagBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\TagBundle\Entity\Tagging;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Provider\ResultStatisticsProvider;

class SearchProvider extends ResultStatisticsProvider
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Oro\Bundle\SearchBundle\Engine\ObjectMapper
     */
    protected $mapper;

    public function __construct(EntityManager $em, ObjectMapper $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($tagId)
    {
        $originResults = $this->em->createQueryBuilder()
            ->select('t')
            ->from('Oro\Bundle\TagBundle\Entity\Tagging', 't')
            ->where('t.tag = :tag')
            ->setParameter('tag', $tagId)
            ->addGroupBy('t.entityName')
            ->addGroupBy('t.recordId')
            ->getQuery()
            ->getResult();

        $results = array();
        /** @var Tagging $item */
        foreach ($originResults as $item) {
            $results[] = new Item(
                $this->em,
                $item->getEntityName(),
                $item->getRecordId(),
                null,
                null,
                null,
                $this->mapper->getEntityConfig($item->getEntityName())
            );
        }

        return new Result(new Query(), $results, count($results));
    }
}
