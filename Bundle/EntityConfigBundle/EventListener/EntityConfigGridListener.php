<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecord;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;

class EntityConfigGridListener implements EventSubscriberInterface
{
    const TYPE_HTML     = 'html';
    const TYPE_TWIG     = 'twig';
    const TYPE_NAVIGATE = 'navigate';
    const TYPE_DELETE   = 'delete';
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
            'oro_datagrid.datgrid.build.after.entityconfig-grid'  => 'onBuildAfter',
            'oro_datagrid.datgrid.build.before.entityconfig-grid' => 'onBuildBefore',
        );
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQuery();

            $this->prepareQuery($queryBuilder);
        }
    }

    /**
     * Add dynamic fields
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        // set new column set with dynamic fields
        $additionalColumns = $this->getDynamicFields();
        $config->offsetAddToArrayByPath(Configuration::COLUMNS_PATH, $additionalColumns);

        // add/configure entity config properties
        $this->addEntityConfigProperties($config);

        // add/configure entity config actions
        $actions = $config->offsetGetByPath(self::PATH_ACTIONS, []);
        $this->prepareRowActions($actions);
        $config->offsetSetByPath(self::PATH_ACTIONS, $actions);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addEntityConfigProperties(DatagridConfiguration $config)
    {
        // configure properties from config providers
        $properties = $config->offsetGetByPath(Configuration::PROPERTIES_PATH, []);
        $filters    = array();
        $actions    = array();

        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions();

            $this->prepareProperties($gridActions, $properties, $actions, $filters, $provider->getScope());

            if ($provider->getPropertyConfig()->getUpdateActionFilter()) {
                $filters['update'] = $provider->getPropertyConfig()->getUpdateActionFilter();
            }
        }

        if (count($filters)) {
            $config->offsetSet(
                ActionExtension::ACTION_CONFIGURATION_KEY,
                $this->getActionConfigurationClosure($filters, $actions)
            );
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
                $actions           = array_map(
                    function () {
                        return false;
                    },
                    $actions
                );
                $actions['update'] = false;
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
     * @TODO fix adding actions from different scopes such as EXTEND
     *
     * @param $actions
     * @param $type
     */
    protected function prepareRowActions(&$actions, $type = PropertyConfigContainer::TYPE_ENTITY)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            $gridActions = $provider->getPropertyConfig()->getGridActions($type);

            foreach ($gridActions as $config) {
                $configItem = array(
                    'name'    => strtolower($config['name']),
                    'options' => array(
                        'label' => ucfirst($config['name']),
                        'icon'  => isset($config['icon']) ? $config['icon'] : 'question-sign',
                        'link'  => strtolower($config['name']) . '_link'
                    )
                );

                if (isset($config['type'])) {
                    switch ($config['type']) {
                        case 'redirect':
                            $configItem['type'] = self::TYPE_NAVIGATE;
                            break;
                    }
                } else {
                    $configItem['type'] = self::TYPE_NAVIGATE;
                }

                $actions[] = $configItem;
            }
        }
    }

    /**
     * @param QueryBuilder $query
     *
     * @return $this
     */
    protected function prepareQuery(QueryBuilder $query)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                $alias     = 'cev' . $code;
                $fieldName = $provider->getScope() . '_' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    'ce.values',
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
     * @return array
     */
    protected function getDynamicFields()
    {
        $fields = [];

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                if (!isset($item['grid'])) {
                    continue;
                }

                $fieldName    = $provider->getScope() . '_' . $code;
                $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);
                $item['grid'] = $this->mapEntityConfigTypes($item['grid']);

                $field = array(
                    $fieldName => array_merge(
                        $item['grid'],
                        array(
                            'expression' => 'cev' . $code . '.value',
                            'field_name' => $fieldName,
                        )
                    )
                );

                if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
                    $fields[$item['options']['priority']] = $field;
                } else {
                    $fields[] = $field;
                }
            }
        }

        ksort($fields);

        $orderedFields = [];
        foreach ($fields as $field) {
            $orderedFields = array_merge($orderedFields, $field);
        }

        return $orderedFields;
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
