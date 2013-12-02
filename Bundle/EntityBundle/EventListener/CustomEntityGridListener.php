<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\EventListener\AbstractConfigGridListener;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

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
        // nothing to do here, just leave it empty
    }

    /**
     * @param BuildBefore $event
     * @return bool
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $entityClass = $this->getRequestParam('class_name');
        if (empty($entityClass)) {
            $entityClass = $this->request->attributes->get('id');
        }

        if (empty($this->entityClass) && $entityClass !== false) {
            $this->entityClass = str_replace('_', '\\', $entityClass);
        }

        if (empty($this->entityClass)) {
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

        // set entity to select from
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
            if ($extendConfig->get('state') != ExtendManager::STATE_NEW && !$extendConfig->get('is_deleted')) {
                list($field, $selectField) = $this->getDynamicFieldItem($alias, $extendConfig);

                if (!empty($field)) {
                    $fields[] = $field;
                }

                if (!empty($selectField)) {
                    $select[] = $selectField;
                }
            }
        }

        ksort($fields);

        $orderedFields = $sorters = $filters = [];
        // compile field list with pre-defined order
        foreach ($fields as $field) {
            $orderedFields = array_merge($orderedFields, $field);
        }

        $result = [
            'columns' => $orderedFields,
        ];

        if (!empty($select)) {
            $result = array_merge(
                $result,
                ['source' => [
                    'query' => ['select' => $select],
                    ]
                ]
            );
        }

        return $result;
    }

    /**
     * Get dynamic field or empty array if field is not visible
     *
     * @param $alias
     * @param ConfigInterface $extendConfig
     * @return array
     */
    public function getDynamicFieldItem($alias, ConfigInterface $extendConfig)
    {
        /** @var FieldConfigId $fieldConfig */
        $fieldConfig = $extendConfig->getId();

        /** @var ConfigProvider $datagridProvider */
        $datagridConfigProvider = $this->configManager->getProvider('datagrid');
        $datagridConfig         = $datagridConfigProvider->getConfig(
            $this->entityClass,
            $fieldConfig->getFieldName()
        );

        $select = '';
        $field = [];
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

            $field = $this->createFieldArrayDefinition($code, $label, $fieldConfig);
            $select = $alias . '.' . $code;
        }

        return [$field, $select];
    }

    /**
     * @param string $code
     * @param $label
     * @param FieldConfigId $fieldConfig
     *
     * @return array
     */
    protected function createFieldArrayDefinition($code, $label, FieldConfigId $fieldConfig)
    {
        return [
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
            $className = $requestParams->get('class_name');
            return $router->generate(
                $route,
                array(
                    'entity_id' => str_replace('\\', '_', $className),
                    'id' => $record->getValue('id')
                )
            );
        };
    }

    /**
     * Trying to get request param
     * - first from current request query
     * - then from master request attributes
     *
     * @param $paramName
     * @param bool $default
     * @return mixed
     */
    protected function getRequestParam($paramName, $default = false)
    {
        $paramValue = $this->requestParams->get($paramName, $default);
        if ($paramValue === false) {
            $paramNameCamelCase = str_replace(
                ' ',
                '',
                lcfirst(
                    ucwords(str_replace('_', ' ', $paramName))
                )
            );

            $paramValue = $this->request->attributes->get($paramNameCamelCase, $default);
        }

        return $paramValue;
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
