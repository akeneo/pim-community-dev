<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
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

    protected $relation;

    protected $hasAssignedExpression;

    /**
     * @var array
     */
    public $additionalParameters = array();

    /**
     * @param ConfigInterface $fieldConfig
     */
    public function setRelationConfig(ConfigInterface $fieldConfig)
    {
        $this->relationConfig = $fieldConfig;
    }

    /**
     * @param array $parameters
     */
    public function setAdditionalParameters(array $parameters)
    {
        $this->additionalParameters = $parameters;
    }

    public function setRelation($relation)
    {
        $this->relation = $relation;
        $this->routeGenerator->setRouteParameters(
            array(
                'id'        => $relation->getId() ? : 0,
                'className' => $this->relationConfig->getId()->getClassName(),
                'fieldName' => $this->relationConfig->getId()->getFieldName()
            )
        );
    }

    public function getRelation()
    {
        if (!$this->relation) {
            throw new \LogicException('Datagrid manager has no configured relation entity');
        }

        return $this->relation;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn               = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn            = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);

        $parameters = array(
            'data_in' => $dataIn,
            'data_not_in' => $dataNotIn
        );

        if ($this->getRelation()->getId()) {
            $parameters = array_merge(array('relation' => $this->getRelation()), $parameters);
        }

        return $parameters;
    }

    /**
     * @return string
     */
    protected function getHasAssignedExpression()
    {
        $classArray = explode('\\', $this->relationConfig->getId()->getClassName());
        $fieldName  =
            ExtendConfigDumper::FIELD_PREFIX
            . strtolower(array_pop($classArray)) . '_'
            . $this->relationConfig->getId()->getFieldName();

        if (null === $this->hasAssignedExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

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

    protected function getDefaultParameters()
    {
        $parameters                                             = parent::getDefaultParameters();
        $parameters[ParametersInterface::ADDITIONAL_PARAMETERS] = $this->additionalParameters;

        return $parameters;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasRelation = new FieldDescription();
        $fieldHasRelation->setName('assigned');
        $fieldHasRelation->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Assigned'),
                'field_name'      => 'assigned',
                'expression'      => $this->getHasAssignedExpression(), //'r.id',
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => false,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldHasRelation);

        parent::configureFields($fieldsCollection);
    }

    /**
     * {@inheritDoc}
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
                            'show_filter' => true,
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
            'assigned' => SorterInterface::DIRECTION_DESC,
        );
    }

    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->addSelect($this->getHasAssignedExpression() . ' as assigned', true);
    }
}
