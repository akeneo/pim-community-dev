<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class RelationEntityDatagrid extends CustomEntityDatagrid
{
    /**
     * @var ConfigInterface
     */
    protected $relationConfig;

    /**
     * @var string
     */
    protected $hasRelationExpression;

    protected $relation;

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        return array();
    }

    /**
     * @param ConfigInterface $fieldConfig
     */
    public function setRelationConfig(ConfigInterface $fieldConfig)
    {
        $this->relationConfig = $fieldConfig;
    }


    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasRelation = new FieldDescription();
        $fieldHasRelation->setName('has_relation');
        $fieldHasRelation->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Assigned'),
                'field_name'      => 'assigned',
                'expression'      => 'r.id',
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldHasRelation);

        parent::configureFields($fieldsCollection);
    }

    public function setRelation($relation)
    {
        $this->relation = $relation;
        $this->routeGenerator->setRouteParameters(
            array(
                'id'        => $relation->getId(),
                'className' => $this->relationConfig->getId()->getClassName(),
                'fieldName' => $this->relationConfig->getId()->getFieldName()
            )
        );
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function getDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fields = array();

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');

        $extendConfigs = $extendConfigProvider->getConfigs($this->entityClass);
        foreach ($extendConfigs as $extendConfig) {
            if ($extendConfig->get('state') != ExtendManager::STATE_NEW && !$extendConfig->get('is_deleted')) {
                /** @var FieldConfigId $fieldConfig */
                $fieldConfig = $extendConfig->getId();
                $fieldName   = $fieldConfig->getFieldName();

                if (in_array($fieldName, $this->relationConfig->get('target_grid'))) {
                    /** @var ConfigProvider $entityConfigProvider */
                    $entityConfigProvider = $this->configManager->getProvider('entity');
                    $entityConfig         = $entityConfigProvider->getConfig($this->entityClass, $fieldName);

                    $label = $entityConfig->get('label') ? : $fieldName;
                    $code  = $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                        ? ExtendConfigDumper::FIELD_PREFIX . $fieldName
                        : $fieldName;

                    $this->queryFields[] = $code;

                    $fieldObject = new FieldDescription();
                    $fieldObject->setName($code);
                    $fieldObject->setOptions(
                        array(
                            'type'        => $this->typeMap[$fieldConfig->getFieldType()],
                            'label'       => $label,
                            'field_name'  => $code,
                            'filter_type' => $this->filterMap[$fieldConfig->getFieldType()],
                            'required'    => false,
                            'sortable'    => true,
                            'filterable'  => true,
                            'show_filter' => false,
                        )
                    );

                    $fields[] = $fieldObject;
                }
            }
        }

        foreach ($fields as $field) {
            $fieldsCollection->add($field);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_relation' => SorterInterface::DIRECTION_DESC,
        );
    }

    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->leftJoin('ce.' . 'field_testentity_rel1', 'r');
        $query->addSelect('r.id as assigned', true);
    }
}
