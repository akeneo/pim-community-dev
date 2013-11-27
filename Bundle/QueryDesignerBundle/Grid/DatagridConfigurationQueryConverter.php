<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid;

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
        $this->selectColumns = [];
        $this->from          = [];
        $this->innerJoins    = [];
        $this->leftJoins     = [];
        parent::doConvert($source);
        $this->selectColumns = null;
        $this->from          = null;
        $this->innerJoins    = null;
        $this->leftJoins     = null;

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
}
