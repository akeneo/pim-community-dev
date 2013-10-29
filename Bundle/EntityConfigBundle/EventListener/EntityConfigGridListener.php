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

class EntityConfigGridListener implements EventSubscriberInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'oro_datagrid.datgrid.build.after.entityconfig-grid' => 'onBuildAfter',
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

            $this->prepareQuery($queryBuilder)
                 ->addDynamicFields($queryBuilder);
        }
    }

    public function onBuilderBefore(BuildBefore $event)
    {

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
     * @param QueryBuilder $query
     * @return $this
     */
    protected function addDynamicFields(QueryBuilder $query)
    {
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {

                $fields = $this->prepareAdditionalFields($provider, $code, $item);
            }
        }

        ksort($fields);
        foreach ($fields as $field) {
//            $fieldsCollection->add($field);
        }

        return $this;
    }

    /**
     * @param ConfigProvider $provider
     * @param string $code
     * @param ConfigIdInterface $item
     *
     * @return array
     */
    protected function prepareAdditionalFields(ConfigProvider $provider, $code, ConfigIdInterface $item)
    {
        $fields = array();

        if (!isset($item['grid'])) {
            return $fields;
        }

        $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);

        $fieldName = $provider->getScope() . '_' . $code;


        $fieldObject = new FieldDescription();
        $fieldObject->setName($fieldName);
        $fieldObject->setOptions(
            array_merge(
                $item['grid'],
                array(
                    'expression' => 'cev' . $code . '.value',
                    'field_name' => $fieldName,
                )
            )
        );

        if (isset($item['grid']['type'])
            && $item['grid']['type'] == FieldDescriptionInterface::TYPE_HTML
            && isset($item['grid']['template'])
        ) {
            $templateDataProperty = new TwigTemplateProperty(
                $fieldObject,
                $item['grid']['template']
            );
            $fieldObject->setProperty($templateDataProperty);
        }

        if (isset($item['options']['priority']) && !isset($fields[$item['options']['priority']])) {
            $fields[$item['options']['priority']] = $fieldObject;
        } else {
            $fields[] = $fieldObject;
        }

        return $fields;
    }

}
