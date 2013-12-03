<?php

namespace Oro\Bundle\EntityBundle\Grid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

class ExtendExtension extends AbstractExtension
{
    const EXTEND_ENTITY_CONFIG_PATH = '[extended_entity_name]';

    /** @var  ConfigManager */
    protected $cm;

    public function __construct(ConfigManager $cm)
    {
        $this->cm = $cm;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(self::EXTEND_ENTITY_CONFIG_PATH, false) !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $entityName = $config->offsetGetByPath(self::EXTEND_ENTITY_CONFIG_PATH);

        $entityProvider = $this->cm->getProvider('entity');
        $fields         = $this->getDynamicFields($entityName);
        /** @var $field FieldConfigId */
        foreach ($fields as $field) {
            $fieldName     = ExtendConfigDumper::FIELD_PREFIX . $field->getFieldName();
            $fieldConfig   = $entityProvider->getConfigById($field);
            $filterOptions = [];
            switch ($field->getFieldType()) {
                case 'integer':
                case 'smallint':
                case 'bigint':
                    $type       = PropertyInterface::TYPE_INTEGER;
                    $filterType = 'number';
                    break;
                case 'decimal':
                case 'float':
                    $type                       = PropertyInterface::TYPE_DECIMAL;
                    $filterType                 = 'number';
                    $filterOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
                    break;
                case 'boolean':
                    $type = $filterType = PropertyInterface::TYPE_BOOLEAN;
                    break;
                case 'date':
                    $type = $filterType = PropertyInterface::TYPE_DATE;
                    break;
                default:
                    $type = $filterType = PropertyInterface::TYPE_STRING;
            }
            $config->offsetSetByPath(
                sprintf('[%s][%s]', FormatterConfiguration::COLUMNS_KEY, $fieldName),
                [
                    'label'                              => $fieldConfig->get('label') ? : $field->getFieldName(),
                    PropertyInterface::FRONTEND_TYPE_KEY => $type
                ]
            );

            $config->offsetSetByPath(
                sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $fieldName),
                [
                    PropertyInterface::DATA_NAME_KEY => $fieldName,
                ]
            );

            $config->offsetSetByPath(
                sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $fieldName),
                [
                    FilterUtility::TYPE_KEY     => $filterType,
                    FilterUtility::DATA_NAME_KEY    => $fieldName,
                    FilterUtility::ENABLED_KEY  => false,
                    FilterUtility::FORM_OPTIONS_KEY => $filterOptions
                ]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $entityName = $config->offsetGetByPath(self::EXTEND_ENTITY_CONFIG_PATH);

        $fields = $this->getDynamicFields($entityName);
        if ($datasource instanceof OrmDatasource && !empty($fields)) {
            /** @var QueryBuilder $qb */
            $qb        = $datasource->getQueryBuilder();
            $fromParts = $qb->getDQLPart('from');

            $alias = false;
            foreach ($fromParts as $fromPart) {
                if ($fromPart->getFrom() == $entityName) {
                    $alias = $fromPart->getAlias();
                }
            }

            if ($alias === false) {
                // add entity if it not exists in from clause
                $alias = ExtendConfigDumper::FIELD_PREFIX . 'o';
                $qb->from($entityName, $alias);
            }

            foreach ($fields as $field) {
                $fn = ExtendConfigDumper::FIELD_PREFIX . $field->getFieldName();
                $qb->addSelect(sprintf('%s.%s', $alias, $fn));

                // set real "data name" for filters and sorters
                $config->offsetSetByPath(
                    sprintf('%s[%s][%s]', OrmSorterConfiguration::COLUMNS_PATH, $fn, PropertyInterface::DATA_NAME_KEY),
                    sprintf('%s.%s', $alias, $fn)
                );
                $config->offsetSetByPath(
                    sprintf('%s[%s][%s]', FilterConfiguration::COLUMNS_PATH, $fn, FilterUtility::DATA_NAME_KEY),
                    sprintf('%s.%s', $alias, $fn)
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return 250;
    }

    /**
     * Get list of dynamic fields to show
     *
     * @param string $entityName
     *
     * @return array
     */
    protected function getDynamicFields($entityName)
    {
        $fields = [];
        if ($this->cm->hasConfig($entityName)) {
            $entityProvider   = $this->cm->getProvider('entity');
            $extendProvider   = $this->cm->getProvider('extend');
            $datagridProvider = $this->cm->getProvider('datagrid');

            $fieldIds = $entityProvider->getIds($entityName);
            foreach ($fieldIds as $fieldId) {
                if ($extendProvider->getConfigById($fieldId)->is('owner', ExtendManager::OWNER_CUSTOM)
                    && $datagridProvider->getConfigById($fieldId)->is('is_visible')
                    && !$extendProvider->getConfigById($fieldId)->is('state', ExtendManager::STATE_NEW)
                    && !$extendProvider->getConfigById($fieldId)->is('is_deleted')
                ) {
                    $fields[] = $fieldId;
                }
            }
        }

        return $fields;
    }
}
