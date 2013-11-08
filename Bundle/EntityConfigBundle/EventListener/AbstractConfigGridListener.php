<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\FilterBundle\Extension\Configuration as FilterConfiguration;

/**
 * Class AbstractConfigGridListener
 * @package Oro\Bundle\EntityConfigBundle\EventListener
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractConfigGridListener implements EventSubscriberInterface
{
    const TYPE_HTML     = 'html';
    const TYPE_TWIG     = 'twig';
    const TYPE_NAVIGATE = 'navigate';
    const TYPE_DELETE   = 'delete';
    const PATH_COLUMNS  = '[columns]';
    const PATH_SORTERS  = '[sorters]';
    const PATH_FILTERS  = '[filters]';
    const PATH_ACTIONS  = '[actions]';

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'oro_datagrid.datgrid.build.after.' . static::GRID_NAME  => 'onBuildAfter',
            'oro_datagrid.datgrid.build.before.' . static::GRID_NAME => 'onBuildBefore',
        );
    }

    /**
     * @param BuildAfter $event
     */
    abstract public function onBuildAfter(BuildAfter $event);

    /**
     * @param BuildBefore $event
     */
    abstract public function onBuildBefore(BuildBefore $event);

    /**
     * Add dynamic fields
     *
     * @param BuildBefore $event
     * @param string|null  $alias
     * @param string|null  $itemType
     * @param bool         $dynamicFirst flag if true - dynamic columns will be placed before static, false - after
     */
    public function doBuildBefore(BuildBefore $event, $alias = null, $itemType = null, $dynamicFirst = true)
    {
        $config = $event->getConfig();

        // get dynamic columns and merge them with static columns from configuration
        $additionalColumnSettings = $this->getDynamicFields($alias, $itemType);
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

            // merge additional items with existing
            if ($dynamicFirst) {
                $items = array_merge_recursive($additionalColumnSettings[$itemName], $items);
            } else {
                $items = array_merge_recursive($items, $additionalColumnSettings[$itemName]);
            }

            // set new item set with dynamic columns/sorters/filters
            $config->offsetSetByPath($path, $items);
        }

        // add/configure entity config properties
        $this->addEntityConfigProperties($config, $itemType);

        // add/configure entity config actions
        $actions = $config->offsetGetByPath(self::PATH_ACTIONS, []);
        $this->prepareRowActions($actions, $itemType);
        $config->offsetSetByPath(self::PATH_ACTIONS, $actions);
    }

    /**
     * Get dynamic fields from config providers
     *
     * @param string|null $alias
     * @param string|null $itemsType
     *
     * @return array
     */
    protected function getDynamicFields($alias = null, $itemsType = null)
    {
        $fields = [];

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems($itemsType) as $code => $item) {
                if (!isset($item['grid'])) {
                    continue;
                }

                $fieldName    = $provider->getScope() . '_' . $code;
                $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);
                $item['grid'] = $this->mapEntityConfigTypes($item['grid']);

                $field = [
                    $fieldName => array_merge(
                        $item['grid'],
                        [
                            'expression' => $alias . $code . '.value',
                            'field_name' => $fieldName,
                        ]
                    )
                ];

                if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
                    $fields[$item['options']['priority']] = $field;
                } else {
                    $fields[] = $field;
                }
            }
        }

        ksort($fields);

        $orderedFields = [];
        // compile field list with pre-defined order
        foreach ($fields as $field) {
            $orderedFields = array_merge($orderedFields, $field);
        }

        return $orderedFields;
    }

    /**
     * @param array $orderedFields
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getDynamicSortersAndFilters(array $orderedFields)
    {
        $filters = $sorters = [];

        // add sorters and filters if needed
        foreach ($orderedFields as $fieldName => $field) {
            if (isset($field['sortable']) && $field['sortable']) {
                $sorters['columns'][$fieldName] = ['data_name' => $field['expression']];
            }

            if (isset($field['filterable']) && $field['filterable']) {
                $filters['columns'][$fieldName] = [
                    'data_name'                      => isset($field['expression']) ? $field['expression'] : $fieldName,
                    'type'                           => isset($field['filter_type']) ? $field['filter_type'] : 'string',
                    'frontend_type'                  => $field['frontend_type'],
                    'label'                          => $field['label'],
                    FilterConfiguration::ENABLED_KEY => isset($field['show_filter']) ? $field['show_filter'] : true,
                ];

                if (isset($field['choices'])) {
                    $filters['columns'][$fieldName]['options']['field_options']['choices'] = $field['choices'];
                }
            }
        }

        return ['filters' => $filters, 'sorters' => $sorters];
    }

    /**
     * @TODO fix adding actions from different scopes such as EXTEND
     *
     * @param array  $actions
     * @param string $type
     */
    protected function prepareRowActions(&$actions, $type)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions($type);

            foreach ($gridActions as $config) {
                $configItem = array(
                    'label' => ucfirst($config['name']),
                    'icon'  => isset($config['icon']) ? $config['icon'] : 'question-sign',
                    'link'  => strtolower($config['name']) . '_link'
                );

                if (isset($config['type'])) {
                    switch ($config['type']) {
                        case 'redirect':
                            $configItem['type'] = self::TYPE_NAVIGATE;
                            break;
                        default:
                            $configItem['type'] = $config['type'];
                            break;
                    }
                } else {
                    $configItem['type'] = self::TYPE_NAVIGATE;
                }

                $actions = array_merge($actions, [strtolower($config['name']) => $configItem]);
            }
        }
    }

    /**
     * @param DatagridConfiguration $config
     * @param                       $itemType
     */
    protected function addEntityConfigProperties(DatagridConfiguration $config, $itemType)
    {
        // configure properties from config providers
        $properties = $config->offsetGetOr(Configuration::PROPERTIES_KEY, []);
        $filters    = array();
        $actions    = array();

        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions($itemType);

            $this->prepareProperties($gridActions, $properties, $actions, $filters, $provider->getScope());

            // TODO: check if this neccessary for field config grid
            if (static::GRID_NAME == 'entityconfig-grid' && $provider->getPropertyConfig()->getUpdateActionFilter()) {
                $filters['update'] = $provider->getPropertyConfig()->getUpdateActionFilter();
            }
        }

        if (count($filters)) {
            $config->offsetSet(
                ActionExtension::ACTION_CONFIGURATION_KEY,
                $this->getActionConfigurationClosure($filters, $actions)
            );
        }
        $config->offsetSet(Configuration::PROPERTIES_KEY, $properties);
    }

    /**
     * @param $gridActions
     * @param $properties
     * @param $actions
     * @param $filters
     * @param $scope
     */
    protected function prepareProperties($gridActions, &$properties, &$actions, &$filters, $scope)
    {
        foreach ($gridActions as $config) {
            $properties[strtolower($config['name']) . '_link'] = [
                'type'   => 'url',
                'route'  => $config['route'],
                'params' => (isset($config['args']) ? $config['args'] : [])
            ];

            if (isset($config['filter'])) {
                $keys = array_map(
                    function ($item) use ($scope) {
                        return $scope . '_' . $item;
                    },
                    array_keys($config['filter'])
                );

                $config['filter']                     = array_combine($keys, $config['filter']);
                $filters[strtolower($config['name'])] = $config['filter'];
            }

            $actions[strtolower($config['name'])] = true;
        }
    }

    /**
     * Returns closure that will configure actions for each row in grid
     *
     * @param array $filters
     * @param array $actions
     *
     * @return callable
     */
    public function getActionConfigurationClosure($filters, $actions)
    {
        return function (ResultRecord $record) use ($filters, $actions) {
            if ($record->getValue('mode') == ConfigModelManager::MODE_READONLY) {
                $actions = array_map(
                    function () {
                        return false;
                    },
                    $actions
                );

                $actions['update']   = false;
                $actions['rowClick'] = false;
            } else {
                foreach ($filters as $action => $filter) {
                    foreach ($filter as $key => $value) {
                        if (is_array($value)) {
                            $error = true;
                            foreach ($value as $v) {
                                if ($record->getValue($key) == $v) {
                                    $error = false;
                                }
                            }
                            if ($error) {
                                $actions[$action] = false;
                                break;
                            }
                        } else {
                            if ($record->getValue($key) != $value) {
                                $actions[$action] = false;
                                break;
                            }
                        }
                    }
                }
            }

            return $actions;
        };
    }

    /**
     * @param QueryBuilder $query
     * @param string       $rootAlias
     * @param              $joinAlias
     * @param string       $itemsType
     *
     * @return $this
     */
    protected function prepareQuery(QueryBuilder $query, $rootAlias, $joinAlias, $itemsType)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems($itemsType) as $code => $item) {
                $alias     = $joinAlias . $code;
                $fieldName = $provider->getScope() . '_' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    $rootAlias . '.values',
                    $alias,
                    'WITH',
                    $alias . ".code='" . $code . "' AND " . $alias . ".scope='" . $provider->getScope() . "'"
                );
                $query->addSelect($alias . '.value as ' . $fieldName);
            }
        }

        return $this;
    }

    /**
     * @param array $gridConfig
     *
     * @return array
     */
    protected function mapEntityConfigTypes(array $gridConfig)
    {
        if (isset($gridConfig['type'])
            && $gridConfig['type'] == self::TYPE_HTML
            && isset($item['grid']['template'])
        ) {
            $gridConfig['type']          = self::TYPE_TWIG;
            $gridConfig['frontend_type'] = self::TYPE_HTML;
        } else {
            if (!empty($gridConfig['type'])) {
                $gridConfig['frontend_type'] = $gridConfig['type'];
            }

            $gridConfig['type'] = 'field';
        }

        return $gridConfig;
    }
}
