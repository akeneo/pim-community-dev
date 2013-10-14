<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\QueryCountCalculator;

class ProxyQuery implements ProxyQueryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $parameterUniqueId;

    /**
     * @var string
     */
    protected $entityJoinAliases;

    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var string
     */
    protected $rootAlias;

    /**
     * @var array
     */
    protected $queryHints = array();

    public function __construct($queryBuilder)
    {
        $this->queryBuilder      = $queryBuilder;
        $this->uniqueParameterId = 0;
        $this->entityJoinAliases = array();
    }

    /**
     * Get query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Get the total number of records
     *
     * @return int
     */
    public function getTotalCount()
    {
        $query = $this->getCountQueryBuilder()
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy')
            ->getQuery();

        $this->applyQueryHints($query);

        return QueryCountCalculator::calculateCount($query);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        $query = $this->getResultQueryBuilder()->getQuery();

        $this->applyQueryHints($query);

        return $query->execute($params, $hydrationMode);
    }

    /**
     * Get query builder for result query
     *
     * @return QueryBuilder
     */
    protected function getResultQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        return $qb;
    }

    /**
     * Get query builder for result count query
     *
     * @return QueryBuilder
     */
    protected function getCountQueryBuilder()
    {
        return clone $this->getResultQueryBuilder();
    }

    /**
     * Returns TRUE if $dql contains usage of parameter with $parameterName
     *
     * @param  string $dql
     * @param  string $parameterName
     *
     * @return bool
     */
    protected function dqlContainsParameter($dql, $parameterName)
    {
        if (is_numeric($parameterName)) {
            $pattern = sprintf('/\?%s[^\w]/', preg_quote($parameterName));
        } else {
            $pattern = sprintf('/\:%s[^\w]/', preg_quote($parameterName));
        }

        return (bool)preg_match($pattern, $dql . ' ');
    }

    /**
     * Checks if select DQL part already has select expression with name
     *
     * @param  QueryBuilder $queryBuilder
     * @param  string       $name
     *
     * @return bool
     */
    protected function hasSelectItem(QueryBuilder $queryBuilder, $name)
    {
        $name = strtolower(trim($name));
        /** @var $select \Doctrine\ORM\Query\Expr\Select */
        foreach ($queryBuilder->getDQLPart('select') as $select) {
            foreach ($select->getParts() as $part) {
                $part = strtolower(trim($part));
                if ($part === $name) {
                    return true;
                } elseif (' as ' . $name === substr($part, -strlen(' as ' . $name))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check whether provided expression already in select clause
     *
     * @param  QueryBuilder $qb
     * @param  string       $selectString
     *
     * @return bool
     */
    protected function isInSelectExpression(QueryBuilder $qb, $selectString)
    {
        /** @var $selectPart \Doctrine\ORM\Query\Expr\Select */
        foreach ($qb->getDQLPart('select') as $selectPart) {
            if (in_array($selectString, $selectPart->getParts())) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addSortOrder($sorter, $direction = null)
    {
        if (empty($sorter['data_name'])) {
            throw new \LogicException('Cannot add sorting order, unknown "data_name" in definition.');
        }

        $sortExpression = $this->getFieldFQN($sorter['data_name']);

        $this->getQueryBuilder()->addOrderBy($sortExpression, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        $aliases = $this->getQueryBuilder()->getRootAliases();
        $alias   = array_shift($aliases);

        $newAlias = 's';

        foreach ($associationMappings as $associationMapping) {
            $newAlias .= '_' . $associationMapping['fieldName'];
            if (!in_array($newAlias, $this->entityJoinAliases)) {
                $this->entityJoinAliases[] = $newAlias;
                $this->getQueryBuilder()
                    ->leftJoin($this->getFieldFQN($associationMapping['fieldName'], $alias), $newAlias);
            }

            $alias = $newAlias;
        }

        return $alias;
    }

    /**
     * Gets the root alias of the query
     *
     * @return string
     */
    public function getRootAlias()
    {
        if (!$this->rootAlias) {
            $this->rootAlias = current($this->getQueryBuilder()->getRootAliases());
        }

        return $this->rootAlias;
    }

    /**
     * Get fields fully qualified name
     *
     * @param  string      $fieldName
     * @param  string|null $parentAlias
     *
     * @return string
     */
    protected function getFieldFQN($fieldName, $parentAlias = null)
    {
        if (strpos($fieldName, '.') === false) { // add the current alias
            $fieldName = ($parentAlias ? : $this->getRootAlias()) . '.' . $fieldName;
        }

        return $fieldName;
    }

    /**
     * Sets a query hint
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return ProxyQuery
     */
    public function setQueryHint($name, $value)
    {
        $this->queryHints[$name] = $value;

        return $this;
    }

    /**
     * Get a list of query hints
     *
     * @return array
     */
    public function getQueryHints()
    {
        return $this->queryHints;
    }

    /**
     * @param AbstractQuery $query
     */
    protected function applyQueryHints(AbstractQuery $query)
    {
        foreach ($this->queryHints as $name => $value) {
            $query->setHint($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->queryBuilder, $name), $args);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->queryBuilder->$name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleScalarResult()
    {
        /** @var Query $query */
        $query = $this->queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueParameterId()
    {
        return $this->uniqueParameterId++;
    }
}
