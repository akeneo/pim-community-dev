<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchPropertyConfig;
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
     * @var SearchPropertyConfig[]
     */
    protected $searchPropertiesConfig;

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
     * @param SearchPropertyConfig[] $searchProperties
     * @param string $entityAlias
     */
    public function __construct(QueryBuilder $queryBuilder, array $searchProperties, $entityAlias = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->searchPropertiesConfig = $searchProperties;
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

        foreach ($this->searchPropertiesConfig as $searchPropertyConfig) {
            $expression = $this->createSearchPropertyExpression(
                $queryBuilder,
                $searchPropertyConfig,
                $query
            );

            if ($searchPropertyConfig->getOption('having', false)) {
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
     * @param SearchPropertyConfig $config
     * @param string $search
     * @return Expr\Base
     */
    protected function createSearchPropertyExpression(
        QueryBuilder $queryBuilder,
        SearchPropertyConfig $config,
        $search
    ) {
        $parameterName = $this->getUniqueParameterName($config);
        $propertyPath = $this->getPropertyPath($config);

        switch ($config->getOperatorType()) {
            case SearchPropertyConfig::OPERATOR_TYPE_START_WITH:
                $result = $this->exprFactory->like($propertyPath, $parameterName);
                $queryBuilder->setParameter($parameterName, $search . '%');
                break;

            case SearchPropertyConfig::OPERATOR_TYPE_CONTAINS: default:
                $result = $this->exprFactory->like($propertyPath, $parameterName);
                $queryBuilder->setParameter($parameterName, '%' . $search . '%');
                break;
        }

        return $result;
    }

    /**
     * @param SearchPropertyConfig $config
     * @return string
     */
    protected function getUniqueParameterName(SearchPropertyConfig $config)
    {
        return $config->getProperty() . '_' . $config->getOperatorType() . '_' . $this->uniqueParametersCounter++;
    }

    /**
     * @param SearchPropertyConfig $config
     * @return string
     */
    protected function getPropertyPath(SearchPropertyConfig $config)
    {
        return $config->getOption('entity_alias', $this->entityAlias) . '.' . $config->getProperty();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function applySorting(QueryBuilder $queryBuilder)
    {
        foreach ($this->searchPropertiesConfig as $searchPropertyConfig) {
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
