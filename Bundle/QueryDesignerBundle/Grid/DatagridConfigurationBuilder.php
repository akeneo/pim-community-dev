<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;

class DatagridConfigurationBuilder
{
    const COLUMN_ALIAS_TEMPLATE = 'c%d';
    const TABLE_ALIAS_TEMPLATE  = 't%d';

    /**
     * @var DatagridConfiguration
     */
    protected $config;

    /**
     * @var int
     */
    protected $columnAliasIndex = 0;

    /**
     * @var int
     */
    protected $tableAliasIndex = 0;

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
    protected $selectStatement;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $orderByStatement;

    public function __construct($gridName, AbstractQueryDesigner $source)
    {
        $this->config     = DatagridConfiguration::createNamed($gridName, []);
        $this->entity     = $source->getEntity();
        $this->definition = json_decode($source->getDefinition(), true);

        if (!isset($this->definition['columns'])) {
            throw new InvalidConfigurationException('The "columns" definition does not exist.');
        }
        if (empty($this->definition['columns'])) {
            throw new InvalidConfigurationException('The "columns" definition must not be empty.');
        }

        $this->config->offsetSetByPath('[source][type]', 'orm');
        $this->buildQuery();
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    protected function buildQuery()
    {
        $this->prepareAliases();
        $this->addColumns();
        $this->addFromStatement();
        $this->addJoinStatements();
    }

    protected function prepareAliases()
    {
        $this->addAliasesForJoinIdentifiers(['']);
        if (isset($this->definition['filters'])) {
            foreach ($this->definition['filters'] as $filter) {
                $this->addAliasesForJoinIdentifiers($this->getJoinIdentifiers($filter['columnName']));
            }
        }
        foreach ($this->definition['columns'] as $column) {
            $this->addAliasesForJoinIdentifiers($this->getJoinIdentifiers($column['name']));
        }
        $this->config->offsetSetByPath('[source][query_config][table_aliases]', $this->aliases);
    }

    protected function addColumns()
    {
        $columnAliases = [];
        $selectColumns = [];
        foreach ($this->definition['columns'] as $column) {
            $tableAlias                     = $this->getTableAliasForColumn($column['name']);
            $columnAlias                    = sprintf(self::COLUMN_ALIAS_TEMPLATE, ++$this->columnAliasIndex);
            $selectColumns[]                = sprintf(
                '%s.%s as %s',
                $tableAlias,
                $this->getFieldName($column['name']),
                $columnAlias
            );
            $columnAliases[$column['name']] = $columnAlias;
            $this->config->offsetSetByPath(
                sprintf('[columns][%s]', $columnAlias),
                ['label' => isset($column['label']) ? $column['label'] : $this->getFieldName($column['name'])]
            );
            if (isset($column['sorting']) && $column['sorting'] !== '') {
                $this->config->offsetSetByPath(
                    sprintf('[sorters][default][%s]', $columnAlias),
                    $column['sorting']
                );
            }
        }

        $this->config->offsetSetByPath('[source][query][select]', $selectColumns);
        $this->config->offsetSetByPath('[source][query_config][column_aliases]', $columnAliases);
    }

    protected function addFromStatement()
    {
        $this->config->offsetSetByPath(
            '[source][query][from]',
            [['table' => $this->entity, 'alias' => $this->aliases['']]]
        );
    }

    protected function addJoinStatements()
    {
        $innerJoins = [];
        $leftJoins = [];
        foreach ($this->aliases as $joinId => $alias) {
            if ($joinId !== '') {
                $parentJoinId = $this->getParentJoinIdentifier($joinId);
                $leftJoins[] = [
                    'join'  => sprintf('%s.%s', $this->aliases[$parentJoinId], $this->getFieldName($joinId)),
                    'alias' => $alias
                ];
            }
        }
        if (!empty($innerJoins)) {
            $this->config->offsetSetByPath('[source][query][join][inner]', $innerJoins);
        }
        if (!empty($leftJoins)) {
            $this->config->offsetSetByPath('[source][query][join][left]', $leftJoins);
        }
    }

    protected function addAliasesForJoinIdentifiers(array $joinIds)
    {
        foreach ($joinIds as $joinId) {
            if (!isset($this->aliases[$joinId])) {
                $this->aliases[$joinId] = sprintf(self::TABLE_ALIAS_TEMPLATE, ++$this->tableAliasIndex);
            }
        }
    }

    /**
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
            if (empty($result)) {
                $result[] = $item;
            } else {
                $result[] = sprintf('%s,%s', $result[count($result) - 1], $item);
            }
        }

        return $result;
    }

    /**
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

    protected function getFieldName($columnNameOrJoinId)
    {
        $lastDelimiter = strrpos($columnNameOrJoinId, '::');
        if (false === $lastDelimiter) {
            return $columnNameOrJoinId;
        }


        return substr($columnNameOrJoinId, strrpos($columnNameOrJoinId, '::') + 2);
    }

    /**
     * @param string $columnName
     * @return string
     */
    protected function getTableAliasForColumn($columnName)
    {
        $joinId = sprintf('%s::%s', $this->entity, $columnName);
        $joinId = $this->getParentJoinIdentifier($joinId);

        return $this->aliases[$joinId];
    }

    /**
     * @param string $columnName
     * @return array
     */
    protected function parseColumn($columnName)
    {
        $result = [];
        $index  = 0;
        foreach (explode(',', $columnName) as $column) {
            if ($index === 0) {
                $result[] = ['entity' => $this->entity, 'name' => $column];
            } else {
                $pair     = explode('::', $column);
                $result[] = ['entity' => $pair[0], 'name' => $pair[1]];
            }
            $index++;
        }

        return $result;
    }
}
