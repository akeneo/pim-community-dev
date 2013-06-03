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
     * @var Expr
     */
    protected $exprFactory;

    /**
     * @var int
     */
    private $uniqueParametersCounter = 0;

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
        $this->exprFactory = new Expr();
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $page, $perPage)
    {
        $queryBuilder = $this->getApplyQueryBuilder();
        $this->applyFiltering($queryBuilder, $search);
        $this->applySorting($queryBuilder);
        $this->applyPagination($queryBuilder, $page, $perPage);
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
     * @param string $query
     */
    protected function applyFiltering(QueryBuilder $queryBuilder, $query)
    {
        $whereSearchExpr = $this->exprFactory->orX();
        $havingSearchExpr = $this->exprFactory->orX();

        foreach ($this->properties as $property) {
            $expression = $this->createSearchPropertyExpression(
                $queryBuilder,
                $property,
                $query
            );

            if ($property->getOption('having', false)) {
                $havingSearchExpr->add($expression);
            } else {
                $whereSearchExpr->add($expression);
            }
        }

        if ($whereSearchExpr->count()) {
            $queryBuilder->andWhere($whereSearchExpr);
        }

        if ($havingSearchExpr->count()) {
            $queryBuilder->andHaving($havingSearchExpr);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Property $property
     * @param string $search
     * @return Expr\Base
     */
    protected function createSearchPropertyExpression(
        QueryBuilder $queryBuilder,
        Property $property,
        $search
    ) {
        $parameterName = $this->getUniqueParameterName($property);
        $propertyPath = $this->getPropertyPath($property);

        switch ($property->getOperatorType()) {
            case Property::OPERATOR_TYPE_START_WITH:
                $result = $this->exprFactory->like($propertyPath, ':' . $parameterName);
                $queryBuilder->setParameter($parameterName, $search . '%');
                break;

            case Property::OPERATOR_TYPE_CONTAINS: default:
                $result = $this->exprFactory->like($propertyPath, ':' . $parameterName);
                $queryBuilder->setParameter($parameterName, '%' . $search . '%');
                break;
        }

        return $result;
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function getUniqueParameterName(Property $property)
    {
        return $property->getName() . '_' . $property->getOperatorType() . '_' . $this->uniqueParametersCounter++;
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
     * @param int $page
     * @param int $perPage
     */
    protected function applyPagination(QueryBuilder $queryBuilder, $page, $perPage)
    {
        if (null !== $perPage) {
            $queryBuilder->setFirstResult($page * $perPage);
            $queryBuilder->setMaxResults($perPage);
        } else {
            $queryBuilder->setFirstResult($page);
        }
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
