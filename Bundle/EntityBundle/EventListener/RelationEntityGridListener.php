<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class RelationEntityGridListener extends CustomEntityGridListener
{
    const GRID_NAME = 'entity-relation-grid';

    /**
     * @var ConfigInterface
     */
    protected $relationConfig;

    /** @var */
    protected $relation;

    /** @var */
    protected $hasAssignedExpression;

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQueryBuilder();

            $added   = $this->request->get('added');
            $removed = $this->request->get('removed');

            if ($added) {
                $added = explode(',', $added);
            } else {
                $added = [0];
            }

            if ($removed) {
                $removed = explode(',', $removed);
            } else {
                $removed = [0];
            }

            $parameters = [
                'data_in' => $added,
                'data_not_in' => $removed
            ];

            if ($this->relation->getId() != null) {
                $parameters['relation'] = $this->relation;
            }

            $queryBuilder->setParameters($parameters);
        }
    }

    /**
     * @param BuildBefore $event
     * @return bool
     */
    public function onBuildBefore(BuildBefore $event)
    {
        // get field config, extendEntity, $added, $removed
        $extendEntityName = $this->getRequestParam('class_name');
        $extendEntityName = str_replace('_', '\\', $extendEntityName);
        $fieldName = $this->getRequestParam('field_name');
        $entityId = $this->getRequestParam('id');

        /** @var ConfigProvider $entityConfigProvider */
        //$entityConfigProvider = $this->configManager->getProvider('entity');
        $extendConfigProvider = $this->configManager->getProvider('extend');

        //$entityConfig = $entityConfigProvider->getConfig($extendEntityName);
        $fieldConfig  = $extendConfigProvider->getConfig($extendEntityName, $fieldName);

        $this->entityClass = $fieldConfig->get('target_entity');
        $this->relationConfig = $fieldConfig;

        // set extendEntity
        $extendEntity = $this->configManager
            ->getEntityManager()
            ->getRepository($extendEntityName)
            ->find($entityId);
        if (!$extendEntity) {
            $extendEntity = new $extendEntityName;
        }
        $this->relation = $extendEntity;

        parent::onBuildBefore($event);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDynamicFields($alias = null, $itemsType = null)
    {
        $result = parent::getDynamicFields($alias, $itemsType);

        $result = array_merge_recursive(
            $result,
            ['source' => [
                'query' => ['select' => [$this->getHasAssignedExpression() . ' as assigned']],
                ]
            ]
        );

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
        $fieldName   = $fieldConfig->getFieldName();

        $field = [];
        $select = ''; // no need to add to select enything here

        if (in_array($fieldName, $this->relationConfig->get('target_grid'))) {
            /** @var ConfigProvider $entityConfigProvider */
            $entityConfigProvider = $this->configManager->getProvider('entity');
            $entityConfig         = $entityConfigProvider->getConfig($this->entityClass, $fieldName);

            $label = $entityConfig->get('label') ? : $fieldName;
            $code  = $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                ? ExtendConfigDumper::FIELD_PREFIX . $fieldName
                : $fieldName;

            $this->queryFields[] = $code;

            $field = $field = $this->createFieldArrayDefinition($code, $label, $fieldConfig);
            $select = $alias . '.' . $code;
        }

        return [$field, $select];
    }

    /**
     * @return string
     */
    protected function getHasAssignedExpression()
    {
        $entityConfig = $this->configManager->getProvider('extend')->getConfig(
            $this->relationConfig->getId()->getClassName()
        );
        $relations = $entityConfig->get('relation');
        $relation  = $relations[$this->relationConfig->get('relation_key')];

        $fieldName = ExtendConfigDumper::FIELD_PREFIX . $relation['target_field_id']->getFieldName();

        if (null === $this->hasAssignedExpression) {
            $entityAlias = 'ce';

            $compOperator = $this->relationConfig->getId()->getFieldType() == 'oneToMany'
                ? '='
                : 'MEMBER OF';

            if ($this->getRelation()->getId()) {
                $this->hasAssignedExpression =
                    "CASE WHEN " .
                    "(:relation $compOperator $entityAlias.$fieldName OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasAssignedExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            }
        }

        return $this->hasAssignedExpression;
    }

    /**
     * @return mixed
     * @throws \LogicException
     */
    public function getRelation()
    {
        if (!$this->relation) {
            throw new \LogicException('Datagrid manager has no configured relation entity');
        }

        return $this->relation;
    }
}
