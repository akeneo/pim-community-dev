<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\DataGridBundle\ORM\Query\QueryCountCalculator;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO: This class should be refactored  (BAP-969).
 */
class ProxyQuery implements ProxyQueryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $sortBy;

    /**
     * @var string
     */
    protected $sortOrder;

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
    protected $sortOrderList = array();

    /**
     * @var array
     */
    protected $selectWhitelist = array();

    /**
     * @var array
     */
    protected $queryHints = array();

    /**
     * @param mixed $queryBuilder
     */
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

        $this->applyOrderByParameters($qb);

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
     * @return bool
     */
    protected function dqlContainsParameter($dql, $parameterName)
    {
        if (is_numeric($parameterName)) {
            $pattern = sprintf('/\?%s[^\w]/', preg_quote($parameterName));
        } else {
            $pattern = sprintf('/\:%s[^\w]/', preg_quote($parameterName));
        }

        return (bool) preg_match($pattern, $dql . ' ');
    }

    /**
     * Apply order by part
     *
     * @param  QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    protected function applyOrderByParameters(QueryBuilder $queryBuilder)
    {
        foreach ($this->sortOrderList as $sortOrder) {
            $this->applySortOrderParameters($queryBuilder, $sortOrder);
        }
    }

    /**
     * Apply sorting
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $sortOrder
     */
    protected function applySortOrderParameters(QueryBuilder $queryBuilder, array $sortOrder)
    {
        list($sortExpression, $extraSelect) = $sortOrder;
        if ($extraSelect && !$this->hasSelectItem($queryBuilder, $sortExpression)) {
            $queryBuilder->addSelect($extraSelect);
        }
    }

    /**
     * Checks if select DQL part already has select expression with name
     *
     * @param  QueryBuilder $queryBuilder
     * @param  string       $name
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
    public function addSortOrder(array $parentAssociationMappings, array $fieldMapping, $direction = null)
    {
        $alias = $this->entityJoin($parentAssociationMappings);
        if (!empty($fieldMapping['entityAlias'])) {
            $alias = $fieldMapping['entityAlias'];
        }

        $extraSelect = null;
        if (!empty($fieldMapping['fieldExpression']) && !empty($fieldMapping['fieldName'])) {
            $sortExpression = $fieldMapping['fieldName'];
            $extraSelect = sprintf('%s AS %s', $fieldMapping['fieldExpression'], $fieldMapping['fieldName']);
        } elseif (!empty($fieldMapping['fieldName'])) {
            $sortExpression = $this->getFieldFQN($fieldMapping['fieldName'], $alias);
        } else {
            throw new \LogicException('Cannot add sorting order, unknown field name in $fieldMapping.');
        }

        $this->getQueryBuilder()->addOrderBy($sortExpression, $direction);
        $this->sortOrderList[] = array($sortExpression, $extraSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        $aliases = $this->getQueryBuilder()->getRootAliases();
        $alias = array_shift($aliases);

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
     * Retrieve the column id of the targeted class
     *
     * @return string
     */
    protected function getIdFieldName()
    {
        if (!$this->idFieldName) {
            /** @var $from \Doctrine\ORM\Query\Expr\From */
            $from  = current($this->getQueryBuilder()->getDQLPart('from'));
            $class = $from->getFrom();

            $idNames = $this->getQueryBuilder()
                ->getEntityManager()
                ->getMetadataFactory()
                ->getMetadataFor($class)
                ->getIdentifierFieldNames();

            $this->idFieldName = current($idNames);
        }

        return $this->idFieldName;
    }

    /**
     * Get id field fully qualified name
     *
     * @return string
     */
    protected function getIdFieldFQN()
    {
        return $this->getFieldFQN($this->getIdFieldName());
    }

    /**
     * Get fields fully qualified name
     *
     * @param  string      $fieldName
     * @param  string|null $parentAlias
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
     * Proxy of QueryBuilder::addSelect with flag that specified whether add select to internal whitelist
     *
     * @param  string     $select
     * @param  bool       $addToWhitelist
     * @return ProxyQuery
     */
    public function addSelect($select = null, $addToWhitelist = false)
    {
        if (empty($select)) {
            return $this;
        }

        if (is_array($select)) {
            $selects = $select;
        } else {
            $arguments = func_get_args();
            $lastElement = end($arguments);
            if (is_bool($lastElement)) {
                $selects = array_slice($arguments, 0, -1);
                $addToWhitelist = $lastElement;
            } else {
                $selects = $arguments;
            }
        }

        if ($addToWhitelist) {
            $this->selectWhitelist = array_merge($this->selectWhitelist, $selects);
        }

        $queryBuilder = $this->getQueryBuilder();
        foreach ($selects as $select) {
            if (!$addToWhitelist || $addToWhitelist && !$this->isInSelectExpression($queryBuilder, $select)) {
                $queryBuilder->addSelect($select);
            }
        }

        return $this;
    }

    /**
     * Set query parameter
     *
     * @param  string     $name
     * @param  mixed      $value
     * @return ProxyQuery
     */
    public function setParameter($name, $value)
    {
        $this->getQueryBuilder()->setParameter($name, $value);

        return $this;
    }

    /**
     * Sets a query hint
     *
     * @param  string     $name
     * @param  mixed      $value
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
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $alias        = $this->entityJoin($parentAssociationMappings);
        $this->sortBy = $alias . '.' . $fieldMapping['fieldName'];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
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
    public function setFirstResult($firstResult)
    {
        $this->queryBuilder->setFirstResult($firstResult);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->queryBuilder->getFirstResult();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->queryBuilder->setMaxResults($maxResults);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->queryBuilder->getMaxResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueParameterId()
    {
        return $this->uniqueParameterId++;
    }
}
