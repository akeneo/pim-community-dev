<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;

class EntitySearchHandler implements SearchHandlerInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilderSearchHandler;

    /**
     * @param EntityManager $em
     * @param string $className
     * @param Property[] $properties
     */
    public function __construct(EntityManager $em, $className, array $properties)
    {
        $repository = $em->getRepository($className);
        $queryBuilder = $repository->createQueryBuilder('e');
        $this->queryBuilderSearchHandler = $this->createQueryBuilderSearchHandler($queryBuilder, $properties, 'e');
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        return $this->queryBuilderSearchHandler->search($search, $firstResult, $maxResults);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Property[] $properties
     * @param string $entityAlias
     * @return QueryBuilderSearchHandler
     */
    protected function createQueryBuilderSearchHandler(QueryBuilder $queryBuilder, array $properties, $entityAlias)
    {
        return new QueryBuilderSearchHandler($queryBuilder, $properties, $entityAlias);
    }
}
