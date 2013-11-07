<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
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
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class CustomEntityGridListener extends AbstractConfigGridListener
{
    const GRID_NAME = 'custom-entity-grid';
    const PATH_FROM = '[source][query][from]';

    /** @var ConfigManager */
    protected $configManager;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var null original entity class */
    protected $entityClass = null;

    /** @var array fields to be shown on grid */
    protected $queryFields = [];

    /** @var  integer parent entity id */
    protected $parentId;

    /** @var Router */
    protected $router;

    /** @var Request */
    protected $request;

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
        'string'   => 'string',
        'integer'  => 'number',
        'smallint' => 'number',
        'bigint'   => 'number',
        'boolean'  => 'boolean',
        'decimal'  => 'decimal',
        'date'     => 'datetime',
        'text'     => 'string',
        'float'    => 'decimal',
    );

    /**
     * @param ConfigManager $configManager
     * @param RequestParameters $requestParameters
     * @param Router $router
     */
    public function __construct(
        ConfigManager $configManager,
        RequestParameters $requestParameters,
        Router $router
    ) {
        $this->configManager = $configManager;
        $this->requestParams = $requestParameters;
        $this->router = $router;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQueryBuilder();
        }
    }

    /**
     * @param BuildBefore $event
     * @return bool
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $entityClass = $this->getEntityClass();
        if (empty($entityClass)) {
            return false;
        }

        $config = $event->getConfig();

        // get dynamic columns
        $additionalColumnSettings = $this->getDynamicFields(
            $config->offsetGetByPath('[source][query][from][0][alias]', 'ce')
        );
        $filtersSorters           = $this->getDynamicSortersAndFilters($additionalColumnSettings);
        $additionalColumnSettings = array_merge(
            $additionalColumnSettings,
            [
                'sorters' => $filtersSorters['sorters'],
                'filters' => $filtersSorters['filters'],
            ]
        );

        foreach (['columns', 'sorters', 'filters', 'source'] as $itemName) {
            $path = '[' . $itemName . ']';

            // get already defined items
            $items = $config->offsetGetByPath($path, []);
            $items = array_merge_recursive($items, $additionalColumnSettings[$itemName]);

            // set new item set with dynamic columns/sorters/filters
            $config->offsetSetByPath($path, $items);
        }

        $from = $config->offsetGetByPath(self::PATH_FROM, []);
        $from[0] = array_merge($from[0], ['table' => $this->entityClass]);
        $config->offsetSetByPath('[source][query][from]', $from);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDynamicFields($alias = null, $itemsType = null)
    {
        $fields = $select = [];

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
                            'type'        => 'field',
                            'label'       => $label,
                            'field_name'  => $code,
                            'filter_type' => $this->filterMap[$fieldConfig->getFieldType()],
                            'required'    => false,
                            'sortable'    => true,
                            'filterable'  => true,
                            'show_filter' => true,
                        ]
                    ];
                    $select[] = $alias . '.' . $code;

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

        return [
            'columns' => $orderedFields,
            'source' => [
                'query' => ['select' => $select],
            ]
        ];
    }

    /**
     * @param string $gridName
     * @param string $keyName
     * @param array $node
     *
     * @return callable
     */
    public function getLinkProperty($gridName, $keyName, $node)
    {
        $router    = $this->router;
        if (!isset($node['route'])) {
            return false;
        } else {
            $route = $node['route'];
        }

        $requestParams = $this->requestParams;

        return function (ResultRecord $record) use ($router, $requestParams, $route) {
            $className = $requestParams->get('entity_class');
            return $router->generate(
                $route,
                array(
                    'entity_id' => str_replace('\\', '_', $className),
                    'id' => $record->getValue('id')
                )
            );
        };
    }

    public function getEntityClass()
    {
        if (empty($this->entityClass)) {
            $entityClass = $this->requestParams->get('entity_class');
            if (empty($entityClass)) {
                $entityClass = str_replace('_', '\\', $this->request->attributes->get('id'));
            }

            $this->entityClass = $entityClass;
        }

        return $this->entityClass;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        if ($request instanceof Request) {
            $this->request = $request;
        }
    }
}
