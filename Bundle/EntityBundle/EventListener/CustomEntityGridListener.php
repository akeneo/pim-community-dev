<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\EventListener\AbstractConfigGridListener;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class CustomEntityGridListener extends AbstractConfigGridListener
{
    const GRID_NAME = 'custom-entity-grid';

    /** @var ConfigManager */
    protected $configManager;

    /** @var null original entity class */
    protected $entityClass = null;

    /** @var array fields to be shown on grid */
    protected $queryFields = [];

    /** @var  integer parent entity id */
    protected $parentId;

    protected $filterMap = array(
        'string'   => 'string',
        'integer'  => 'number',
        'smallint' => 'number',
        'bigint'   => 'number',
        'boolean'  => 'boolean',
        'decimal'  => 'number',
        'date'     => 'range',
        'text'     => 'string',
        'float'    => 'number',
    );

    protected $typeMap = array(
        'string'   => 'text',
        'integer'  => 'integer',
        'smallint' => 'integer',
        'bigint'   => 'integer',
        'boolean'  => 'boolean',
        'decimal'  => 'decimal',
        'date'     => 'date',
        'text'     => 'text',
        'float'    => 'decimal',
    );


    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQueryBuilder();

            $this->prepareQuery($queryBuilder, 'ce', 'cev', PropertyConfigContainer::TYPE_ENTITY);
        }
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        // get dynamic columns
        $additionalColumnSettings = $this->getDynamicFields();
        $filtersSorters           = $this->getDynamicSortersAndFilters($additionalColumnSettings);
        $additionalColumnSettings = [
            'columns' => $additionalColumnSettings,
            'sorters' => $filtersSorters['sorters'],
            'filters' => $filtersSorters['filters'],
        ];

        foreach (['columns', 'sorters', 'filters'] as $itemName) {
            $path = '[' . $itemName . ']';

            // get already defined items
            $items = $config->offsetGetByPath($path, []);
            $items = array_merge_recursive($items, $additionalColumnSettings[$itemName]);

            // set new item set with dynamic columns/sorters/filters
            $config->offsetSetByPath($path, $items);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDynamicFields($alias = null, $itemsType = null)
    {
        $fields = [];

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');
        $extendConfigs        = $extendConfigProvider->getConfigs($this->entityClass);

        foreach ($extendConfigs as $extendConfig) {
            if ($extendConfig->get('state') != ExtendManager::STATE_NEW
                && !$extendConfig->get('is_deleted')

            ) {
                /** @var FieldConfigId $fieldConfig */
                $fieldConfig = $extendConfig->getId();

                /** @var ConfigProvider $datagridProvider */
                $datagridConfigProvider = $this->configManager->getProvider('datagrid');
                $datagridConfig         = $datagridConfigProvider->getConfig(
                    $this->entityClass,
                    $fieldConfig->getFieldName()
                );

                if ($datagridConfig->is('is_visible')) {
                    /** @var ConfigProvider $entityConfigProvider */
                    $entityConfigProvider = $this->configManager->getProvider('entity');
                    $entityConfig         = $entityConfigProvider->getConfig(
                        $this->entityClass,
                        $fieldConfig->getFieldName()
                    );

                    $label = $entityConfig->get('label') ?: $fieldConfig->getFieldName();
                    $code  = $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                        ? ExtendConfigDumper::FIELD_PREFIX . $fieldConfig->getFieldName()
                        : $fieldConfig->getFieldName();

                    $this->queryFields[] = $code;

                    $field = [
                        $code => [
                            'type'        => $this->typeMap[$fieldConfig->getFieldType()],
                            'label'       => $label,
                            'field_name'  => $code,
                            'filter_type' => $this->filterMap[$fieldConfig->getFieldType()],
                            'required'    => false,
                            'sortable'    => true,
                            'filterable'  => true,
                            'show_filter' => true,
                        ]
                    ];

                    // add field according to priority if exists
                    if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
                        $fields[$item['options']['priority']] = $field;
                    } else {
                        $fields[] = $field;
                    }
                }
            }
        }

        ksort($fields);

        $orderedFields = $sorters = $filters = [];
        // compile field list with pre-defined order
        foreach ($fields as $field) {
            $orderedFields = array_merge($orderedFields, $field);
        }

        return $orderedFields;
    }
}
