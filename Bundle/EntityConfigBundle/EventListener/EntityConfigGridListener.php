<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\FilterBundle\Extension\Configuration as FilterConfiguration;

class EntityConfigGridListener extends AbstractConfigGridListener
{
    const GRID_NAME = 'entityconfig-grid';


    /** @var ConfigManager */
    protected $configManager;

    /** @var array Filter choices for name and module column filters */
    protected $filterChoices = ['name' => [], 'module' => []];

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQuery();

            $this->prepareQuery($queryBuilder, 'ce', 'cev', PropertyConfigContainer::TYPE_ENTITY);
        }
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $this->doBuildBefore($event, 'cev', PropertyConfigContainer::TYPE_ENTITY);
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
     * @param array $actions
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
     * @param array $orderedFields
     * @return array
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
                    'data_name'     => $field['expression'],
                    'type'          => isset($field['filter_type']) ? $field['filter_type'] : 'string',
                    'frontend_type' => $field['frontend_type'],
                    'label'         => $field['label'],
                    FilterConfiguration::ENABLED_KEY   => isset($field['show_filter']) ? $field['show_filter'] : true,
                ];

                if (isset($field['choices'])) {
                    $filters['columns'][$fieldName]['options']['field_options']['choices'] = $field['choices'];
                }
            }
        }

        return ['filters' => $filters, 'sorters' => $sorters];
    }

    /**
     * Call this method from datagrid.yml
     * invoked in Manager when datagrid configuration prepared for grid build process
     *
     * @return array
     */
    public function getChoicesName()
    {
        return $this->getObjectName();
    }

    /**
     * Call this method from datagrid.yml
     * invoked in Manager when datagrid configuration prepared for grid build process
     *
     * @return array
     */
    public function getChoicesModule()
    {
        return $this->getObjectName('module');
    }

    /**
     *
     * @param  string $scope
     * @return array
     */
    protected function getObjectName($scope = 'name')
    {
        if (empty($this->filterChoices[$scope])) {
            $alias = 'ce';
            $qb = $this->configManager->getEntityManager()->createQueryBuilder();
            $qb->select($alias)
                ->from(EntityConfigModel::ENTITY_NAME, $alias)
                ->add('select', $alias.'.className')
                ->distinct($alias.'.className');

            $result = $qb->getQuery()->getArrayResult();

            $options = ['name' => [], 'module' => []];
            foreach ((array) $result as $value) {
                $className = explode('\\', $value['className']);

                $options['name'][$value['className']]   = '';
                $options['module'][$value['className']] = '';

                if (strpos($value['className'], 'Extend\\Entity') === false) {
                    foreach ($className as $index => $name) {
                        if (count($className) - 1 == $index) {
                            $options['name'][$value['className']] = $name;
                        } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                            $options['module'][$value['className']] .= $name;
                        }
                    }
                } else {
                    $options['name'][$value['className']]   = str_replace('Extend\\Entity\\', '', $value['className']);
                    $options['module'][$value['className']] = 'System';
                }
            }

            $this->filterChoices = $options;
        }

        return $this->filterChoices[$scope];
    }


}
