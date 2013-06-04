<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;

class QueryBuilderSearchHandler implements SearchHandlerInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var ExpressionFactory
     */
    protected $exprFactory;

    /**
     * @param QueryBuilder $queryBuilder
     * @param Property[] $properties
     * @param string $entityAlias
     */
    public function __construct(QueryBuilder $queryBuilder, array $properties, $entityAlias = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->properties = $properties;
        if (!$entityAlias) {
            $entityAlias = reset($this->queryBuilder->getRootAliases());
        }
        $this->entityAlias = $entityAlias;
        $this->exprFactory = new ExpressionFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        $queryBuilder = $this->getApplyQueryBuilder();
        $this->applyFiltering($queryBuilder, $search);
        $this->applySorting($queryBuilder);
        $this->applyPagination($queryBuilder, $firstResult, $maxResults);
        return $this->getResults($queryBuilder);
    }

    /**
     * @return QueryBuilder
     */
    protected function getApplyQueryBuilder()
    {
        return clone $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $search
     */
    protected function applyFiltering(QueryBuilder $queryBuilder, $search)
    {
        $propertiesPath = $this->getPropertiesPath();
        if (count($propertiesPath) == 1) {
            $searchExpr = $propertiesPath[0];
        } else {
            $searchExpr = $this->exprFactory->multipleConcat($propertiesPath, ' ');
        }

        $queryBuilder->andWhere($this->exprFactory->like($searchExpr, ':search'));
        $queryBuilder->setParameter('search', '%' . $search . '%');
    }

    /**
     * @return array
     */
    protected function getPropertiesPath()
    {
        $result = array();
        foreach ($this->properties as $property) {
            $result[] = $this->getPropertyPath($property);
        }
        return $result;
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function getPropertyPath(Property $property)
    {
        return $property->getOption('entity_alias', $this->entityAlias) . '.' . $property->getName();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function applySorting(QueryBuilder $queryBuilder)
    {
        foreach ($this->properties as $searchPropertyConfig) {
            $queryBuilder->addOrderBy(
                $this->getPropertyPath($searchPropertyConfig),
                $searchPropertyConfig->getOption('order', 'ASC')
            );
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param int $firstResult
     * @param int $maxResults
     */
    protected function applyPagination(QueryBuilder $queryBuilder, $firstResult, $maxResults)
    {
        $queryBuilder->setFirstResult($firstResult * $maxResults);
        $queryBuilder->setMaxResults($maxResults);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return array
     */
    protected function getResults(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->getQuery()->execute();
    }
}
