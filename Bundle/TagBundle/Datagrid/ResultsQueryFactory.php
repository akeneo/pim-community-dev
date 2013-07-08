<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Symfony\Component\Routing\Router;

class ResultsQueryFactory extends EntityQueryFactory
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    protected $router;

    /**
     * {@inheritDoc}
     */
    public function __construct(RegistryInterface $registry, $className, ObjectMapper $mapper, Router $router)
    {
        parent::__construct($registry, $className);

        $this->mapper = $mapper;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getEntityManagerForClass($this->className);
        $this->queryBuilder = $entityManager->getRepository($this->className)->createQueryBuilder($this->alias);

        if (!$this->queryBuilder) {
            throw new \LogicException('Can\'t create datagrid query. Query builder is not configured.');
        }

        return new ResultsQuery($this->queryBuilder, $this->mapper, $entityManager, $this->router);
    }
}
