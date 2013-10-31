<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

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
     * @param string $alias
     * @param string $itemType
     */
    public function doBuildBefore(BuildBefore $event, $alias, $itemType)
    {
        $config = $event->getConfig();

        // get dynamic columns and merge them with static columns from configuration
        $additionalColumnSettings = $this->getDynamicFields($alias, $itemType);
        $filtersSorters = $this->getDynamicSortersAndFilters($additionalColumnSettings);
        $additionalColumnSettings = [
            'columns' => $additionalColumnSettings,
            'sorters' => $filtersSorters['sorters'],
            'filters' => $filtersSorters['filters'],
        ];

        foreach (['columns', 'sorters', 'filters'] as $itemName) {
            $path = '['.$itemName.']';
            // get already defined columns, sorters and filters
            $items = $config->offsetGetByPath($path, array());

            // merge additional items with existing
            $items = array_merge_recursive($additionalColumnSettings[$itemName], $items);

            // set new item set with dynamic columns/sorters/filters
            $config->offsetSetByPath($path, $items);
        }

        // add/configure entity config properties
        $this->addEntityConfigProperties($config);

        // add/configure entity config actions
        $actions = $config->offsetGetByPath(self::PATH_ACTIONS, []);
        $this->prepareRowActions($actions);
        $config->offsetSetByPath(self::PATH_ACTIONS, $actions);
    }

    /**
     * @param string $alias
     * @param string $itemsType
     * @return array
     */
    protected function getDynamicFields($alias, $itemsType)
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

                $field = array(
                    $fieldName => array_merge(
                        $item['grid'],
                        array(
                            'expression' => $alias . $code . '.value',
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

        $orderedFields = $sorters = $filters = [];
        // compile field list with pre-defined order
        foreach ($fields as $field) {
            $orderedFields = array_merge($orderedFields, $field);
        }

        return $orderedFields;
    }

    /**
     * @param QueryBuilder $query
     * @param string $rootAlias
     * @param string $alias
     * @param string $itemsType
     *
     * @return $this
     */
    protected function prepareQuery(QueryBuilder $query, $rootAlias, $alias, $itemsType)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems($itemsType) as $code => $item) {
                $alias     = $alias . $code;
                $fieldName = $provider->getScope() . '_' . $code;

                if (isset($item['grid']['query'])) {
                    $query->andWhere($alias . '.value ' . $item['grid']['query']['operator'] . ' :' . $alias);
                    $query->setParameter($alias, $item['grid']['query']['value']);
                }

                $query->leftJoin(
                    $rootAlias.'.values',
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
