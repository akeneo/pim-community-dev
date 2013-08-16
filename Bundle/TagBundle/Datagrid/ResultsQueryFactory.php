<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class ResultsQueryFactory extends EntityQueryFactory
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * {@inheritDoc}
     */
    public function __construct(RegistryInterface $registry, $className, ObjectMapper $mapper)
    {
        parent::__construct($registry, $className);

        $this->mapper = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function createQuery()
    {
        $em = $this->registry->getManagerForClass($this->className);
        $this->queryBuilder = $em->getRepository($this->className)->createQueryBuilder($this->alias);

        return new ResultsQuery($this->queryBuilder, $em, $this->mapper);
    }
}
