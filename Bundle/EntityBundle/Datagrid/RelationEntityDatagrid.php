<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class RelationEntityDatagrid extends CustomEntityDatagrid
{
    protected $relationConfig;

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
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function getDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fields = array();

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');
        $extendConfigs        = $extendConfigProvider->getConfigs($this->entityClass);

        foreach ($extendConfigs as $extendConfig) {
            if ($extendConfig->get('state') != ExtendManager::STATE_NEW
                && !$extendConfig->get('is_deleted')

            ) {
                /** @var FieldConfigId $fieldConfig */
                $fieldConfig = $extendConfig->getId();

                /** @var ConfigProvider $datagridProvider */
                //$datagridConfigProvider = $this->configManager->getProvider('datagrid');
                /*$datagridConfig         = $datagridConfigProvider->getConfig(
                    $this->entityClass,
                    $fieldConfig->getFieldName()
                );*/

                //if ($datagridConfig->is('is_visible')) {
                if (in_array($extendConfig->getId()->getFieldName(), $this->relationConfig->get('target_grid'))) {
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
}
