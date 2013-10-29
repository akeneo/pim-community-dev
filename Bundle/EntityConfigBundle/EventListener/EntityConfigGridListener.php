<?php

namespace Oro\Bundle\EntityConfigBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityConfigGridListener implements EventSubscriberInterface
{
    const PATH_COLUMNS = '[columns]';

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
            'oro_datagrid.datgrid.build.after.entityconfig-grid' => 'onBuildAfter',
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
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $columns = $config->offsetGetByPath(self::PATH_COLUMNS, array());
        $additionalColumns = $this->getDynamicFields();
        $columns = array_merge_recursive($additionalColumns, $columns);

        $config->offsetSetByPath(self::PATH_COLUMNS, $columns);
    }

    /**
     * @param QueryBuilder $query
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
                $query->addSelect($alias . '.value as ' . $fieldName, true);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function getDynamicFields()
    {
        $fields = [];

        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                if (!isset($item['grid'])) {
                    continue;
                }

                $fieldName = $provider->getScope() . '_' . $code;
                $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);

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

        $ordererFields = [];
        foreach ($fields as $field) {
            $ordererFields = array_merge($ordererFields, $field);
        }

        return $ordererFields;
    }
}
