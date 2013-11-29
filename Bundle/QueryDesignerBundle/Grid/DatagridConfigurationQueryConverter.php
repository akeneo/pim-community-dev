<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractOrmQueryConverter;

class DatagridConfigurationQueryConverter extends AbstractOrmQueryConverter
{
    /**
     * @var DatagridConfiguration
     */
    protected $config;

    /**
     * @var array
     */
    protected $selectColumns;

    /**
     * @var array
     */
    protected $from;

    /**
     * @var array
     */
    protected $innerJoins;

    /**
     * @var array
     */
    protected $leftJoins;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var string
     */
    protected $currentFilterPath;

    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct($doctrine);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Converts a query specified in $source parameter to a datagrid configuration
     *
     * @param string                $gridName
     * @param AbstractQueryDesigner $source
     * @return DatagridConfiguration
     */
    public function convert($gridName, AbstractQueryDesigner $source)
    {
        $this->config = DatagridConfiguration::createNamed($gridName, []);
        $this->doConvert($source);

        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    protected function doConvert(AbstractQueryDesigner $source)
    {
        $this->selectColumns     = [];
        $this->from              = [];
        $this->innerJoins        = [];
        $this->leftJoins         = [];
        $this->filters           = [];
        $this->currentFilterPath = '';
        parent::doConvert($source);
        $this->selectColumns     = null;
        $this->from              = null;
        $this->innerJoins        = null;
        $this->leftJoins         = null;
        $this->filters           = null;
        $this->currentFilterPath = null;

        $this->config->offsetSetByPath('[source][type]', 'orm');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTableAliases()
    {
        parent::prepareTableAliases();
        $this->config->offsetSetByPath('[source][query_config][table_aliases]', $this->tableAliases);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareColumnAliases()
    {
        parent::prepareColumnAliases();
        $this->config->offsetSetByPath('[source][query_config][column_aliases]', $this->columnAliases);
    }

    protected function addSelectStatement()
    {
        parent::addSelectStatement();
        $this->config->offsetSetByPath('[source][query][select]', $this->selectColumns);
    }

    /**
     * {@inheritdoc}
     */
    protected function addSelectColumn(
        $entityClassName,
        $tableAlias,
        $fieldName,
        $columnAlias,
        $columnLabel
    ) {
        $this->selectColumns[] = sprintf(
            '%s.%s as %s',
            $tableAlias,
            $fieldName,
            $columnAlias
        );

        $this->config->offsetSetByPath(
            sprintf('[columns][%s]', $columnAlias),
            [
                'label'         => $columnLabel,
                'frontend_type' => $this->getFrontendFieldType($this->getFieldType($entityClassName, $fieldName))
            ]
        );

        // Add sorters
        $this->config->offsetSetByPath(
            sprintf('[sorters][columns][%s]', $columnAlias),
            [
                'data_name' => sprintf('%s.%s', $tableAlias, $fieldName),
            ]
        );

        // Add filters
        $this->config->offsetSetByPath(
            sprintf('[filters][columns][%s]', $columnAlias),
            [
                'type' => $this->getFilterType($this->getFieldType($entityClassName, $fieldName)),
                'data_name' => sprintf('%s.%s', $tableAlias, $fieldName),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addFromStatements()
    {
        parent::addFromStatements();
        $this->config->offsetSetByPath('[source][query][from]', $this->from);
    }

    /**
     * {@inheritdoc}
     */
    protected function addFromStatement($entityClassName, $tableAlias)
    {
        $this->from[] = [
            'table' => $entityClassName,
            'alias' => $tableAlias
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function addJoinStatements()
    {
        parent::addJoinStatements();
        if (!empty($this->innerJoins)) {
            $this->config->offsetSetByPath('[source][query][join][inner]', $this->innerJoins);
        }
        if (!empty($this->leftJoins)) {
            $this->config->offsetSetByPath('[source][query][join][left]', $this->leftJoins);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addJoinStatement($joinTableAlias, $joinFieldName, $joinAlias)
    {
        $this->leftJoins[] = [
            'join'  => sprintf('%s.%s', $joinTableAlias, $joinFieldName),
            'alias' => $joinAlias
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function addWhereStatement()
    {
        parent::addWhereStatement();
        if (!empty($this->filters)) {
            $this->config->offsetSetByPath('[source][query_config][filters]', $this->filters);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function beginWhereGroup()
    {
        $this->currentFilterPath .= '[0]';
        $this->accessor->setValue($this->filters, $this->currentFilterPath, []);
    }

    /**
     * {@inheritdoc}
     */
    protected function endWhereGroup()
    {
        $this->currentFilterPath = substr(
            $this->currentFilterPath,
            0,
            strrpos($this->currentFilterPath, '[')
        );
        if ($this->currentFilterPath !== '') {
            $this->incrementCurrentFilterPath();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addWhereOperator($operator)
    {
        $this->accessor->setValue($this->filters, $this->currentFilterPath, $operator);
        $this->incrementCurrentFilterPath();
    }

    /**
     * {@inheritdoc}
     */
    protected function addWhereCondition(
        $entityClassName,
        $tableAlias,
        $fieldName,
        $columnAlias,
        $filterName,
        array $filterData
    ) {
        $filter = [
            'column'     => sprintf('%s.%s', $tableAlias, $fieldName),
            'filter'     => $filterName,
            'filterData' => $filterData
        ];
        if ($columnAlias) {
            $filter['columnAlias'] = $columnAlias;
        }
        $this->accessor->setValue($this->filters, $this->currentFilterPath, $filter);
        $this->incrementCurrentFilterPath();
    }

    /**
     * Increments last index in the path of filter
     */
    protected function incrementCurrentFilterPath()
    {
        $start                   = strrpos($this->currentFilterPath, '[');
        $index                   = substr(
            $this->currentFilterPath,
            $start + 1,
            strlen($this->currentFilterPath) - $start - 2
        );
        $this->currentFilterPath = sprintf(
            '%s%d]',
            substr($this->currentFilterPath, 0, $start + 1),
            intval($index) + 1
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addOrderByColumn($columnAlias, $columnSorting)
    {
        $this->config->offsetSetByPath(
            sprintf('[sorters][default][%s]', $columnAlias),
            $columnSorting
        );
    }

    /**
     * Gets a datagrid column frontend type for the given field type
     *
     * @param string $fieldType
     * @return string
     */
    protected function getFrontendFieldType($fieldType)
    {
        switch ($fieldType) {
            case 'integer':
            case 'smallint':
            case 'bigint':
                return PropertyInterface::TYPE_INTEGER;
            case 'decimal':
            case 'float':
                return PropertyInterface::TYPE_DECIMAL;
            case 'boolean':
                return PropertyInterface::TYPE_BOOLEAN;
            case 'date':
                return PropertyInterface::TYPE_DATE;
            case 'datetime':
                return PropertyInterface::TYPE_DATETIME;
        }

        return PropertyInterface::TYPE_STRING;
    }

    /**
     * Get filter type for given field type
     *
     * @param string $fieldType
     * @return string
     */
    protected function getFilterType($fieldType)
    {
        switch ($fieldType) {
            case 'integer':
            case 'smallint':
            case 'bigint':
            case 'decimal':
            case 'float':
                return 'number';
            case 'boolean':
                return PropertyInterface::TYPE_BOOLEAN;
            case 'date':
            case 'datetime':
                return PropertyInterface::TYPE_DATETIME;
        }

        return PropertyInterface::TYPE_STRING;
    }
}
