<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;

abstract class AbstractQueryConverter
{
    const COLUMN_ALIAS_TEMPLATE = 'c%d';
    const TABLE_ALIAS_TEMPLATE  = 't%d';

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var array
     */
    protected $definition;

    /**
     * @var array
     */
    protected $tableAliases;

    /**
     * @var array
     */
    protected $columnAliases;

    /**
     * Converts a query from the query designer format to a target format
     *
     * @param AbstractQueryDesigner $source
     * @throws InvalidConfigurationException
     */
    protected function doConvert(AbstractQueryDesigner $source)
    {
        $this->entity     = $source->getEntity();
        $this->definition = json_decode($source->getDefinition(), true);

        if (!isset($this->definition['columns'])) {
            throw new InvalidConfigurationException('The "columns" definition does not exist.');
        }
        if (empty($this->definition['columns'])) {
            throw new InvalidConfigurationException('The "columns" definition must not be empty.');
        }

        $this->tableAliases  = [];
        $this->columnAliases = [];
        $this->buildQuery();
        $this->tableAliases  = null;
        $this->columnAliases = null;
    }

    /**
     * A factory method provides an algorithm used to convert a query
     */
    protected function buildQuery()
    {
        $this->prepareTableAliases();
        $this->prepareColumnAliases();
        $this->addSelectStatement();
        $this->addFromStatements();
        $this->addJoinStatements();
        $this->addOrderByStatement();
    }

    /**
     * Prepares aliases for tables involved to a query
     */
    protected function prepareTableAliases()
    {
        $this->addTableAliasesForJoinIdentifiers(['']);
        if (isset($this->definition['filters'])) {
            foreach ($this->definition['filters'] as $filter) {
                $this->addTableAliasesForJoinIdentifiers($this->getJoinIdentifiers($filter['columnName']));
            }
        }
        foreach ($this->definition['columns'] as $column) {
            $this->addTableAliasesForJoinIdentifiers($this->getJoinIdentifiers($column['name']));
        }
    }

    /**
     * Prepares aliases for columns should be returned by a query
     */
    protected function prepareColumnAliases()
    {
        foreach ($this->definition['columns'] as $column) {
            $this->columnAliases[$column['name']] =
                sprintf(static::COLUMN_ALIAS_TEMPLATE, count($this->columnAliases) + 1);
        }
    }

    /**
     * Performs conversion of SELECT statement
     */
    protected function addSelectStatement()
    {
        foreach ($this->definition['columns'] as $column) {
            $fieldName = $this->getFieldName($column['name']);
            $this->addSelectColumn(
                $this->getEntityClassName($column['name']),
                $this->getTableAliasForColumn($column['name']),
                $fieldName,
                $this->columnAliases[$column['name']],
                isset($column['label']) ? $column['label'] : $fieldName
            );
        }
    }

    /**
     * Performs conversion of a single column of SELECT statement
     */
    abstract protected function addSelectColumn(
        $entityClassName,
        $tableAlias,
        $fieldName,
        $columnAlias,
        $columnLabel
    );

    /**
     * Performs conversion of FROM statement
     */
    protected function addFromStatements()
    {
        $this->addFromStatement($this->entity, $this->tableAliases['']);
    }

    /**
     * Performs conversion of a single table of FROM statement
     */
    abstract protected function addFromStatement($entityClassName, $tableAlias);

    /**
     * Performs conversion of JOIN statements
     */
    protected function addJoinStatements()
    {
        foreach ($this->tableAliases as $joinId => $alias) {
            if ($joinId !== '') {
                $parentJoinId = $this->getParentJoinIdentifier($joinId);
                $this->addJoinStatement(
                    $this->tableAliases[$parentJoinId],
                    $this->getFieldName($joinId),
                    $alias
                );
            }
        }
    }

    /**
     * Performs conversion of a single JOIN statement
     */
    abstract protected function addJoinStatement($joinTableAlias, $joinFieldName, $joinAlias);

    /**
     * Performs conversion of ORDER BY statement
     */
    protected function addOrderByStatement()
    {
        foreach ($this->definition['columns'] as $column) {
            if (isset($column['sorting']) && $column['sorting'] !== '') {
                $this->addOrderByColumn(
                    $this->columnAliases[$column['name']],
                    $column['sorting']
                );
            }
        }
    }

    /**
     * Performs conversion of a single column of ORDER BY statement
     */
    abstract protected function addOrderByColumn($columnAlias, $columnSorting);

    /**
     * Generates and saves aliases for the given joins
     *
     * @param string[] $joinIds
     */
    protected function addTableAliasesForJoinIdentifiers(array $joinIds)
    {
        foreach ($joinIds as $joinId) {
            if (!isset($this->tableAliases[$joinId])) {
                $this->tableAliases[$joinId] = sprintf(static::TABLE_ALIAS_TEMPLATE, count($this->tableAliases) + 1);
            }
        }
    }

    /**
     * Builds a join identifier for the given column
     *
     * @param string $columnName
     * @return string
     */
    protected function getJoinIdentifiers($columnName)
    {
        $lastDelimiter = strrpos($columnName, ',');
        if (false === $lastDelimiter) {
            return [''];
        }

        $result = [];
        $items  = explode(',', sprintf('%s::%s', $this->entity, substr($columnName, 0, $lastDelimiter)));
        foreach ($items as $item) {
            $result[] = empty($result)
                ? $item
                : sprintf('%s,%s', $result[count($result) - 1], $item);
        }

        return $result;
    }

    /**
     * Extracts a parent join identifier
     *
     * @param string $joinId
     * @return string
     */
    protected function getParentJoinIdentifier($joinId)
    {
        $lastDelimiter = strrpos($joinId, ',');
        if (false === $lastDelimiter) {
            return '';
        }

        return substr($joinId, 0, $lastDelimiter);
    }

    /**
     * Extracts an entity class name for the given column or from the given join identifier
     *
     * @param string $columnNameOrJoinId
     * @return string
     */
    protected function getEntityClassName($columnNameOrJoinId)
    {
        $lastDelimiter = strrpos($columnNameOrJoinId, '::');
        if (false === $lastDelimiter) {
            return $this->entity;
        }
        $lastItemDelimiter = strrpos($columnNameOrJoinId, ',');
        if (false === $lastItemDelimiter) {
            return substr($columnNameOrJoinId, 0, $lastDelimiter);
        }

        return substr($columnNameOrJoinId, $lastItemDelimiter + 1, $lastDelimiter - $lastItemDelimiter - 1);
    }

    /**
     * Extracts a field name for the given column or from the given join identifier
     *
     * @param string $columnNameOrJoinId
     * @return string
     */
    protected function getFieldName($columnNameOrJoinId)
    {
        $lastDelimiter = strrpos($columnNameOrJoinId, '::');
        if (false === $lastDelimiter) {
            return $columnNameOrJoinId;
        }

        return substr($columnNameOrJoinId, $lastDelimiter + 2);
    }

    /**
     * Returns a table alias for the given column
     *
     * @param string $columnName
     * @return string
     */
    protected function getTableAliasForColumn($columnName)
    {
        $joinId = sprintf('%s::%s', $this->entity, $columnName);
        $joinId = $this->getParentJoinIdentifier($joinId);

        return $this->tableAliases[$joinId];
    }
}
