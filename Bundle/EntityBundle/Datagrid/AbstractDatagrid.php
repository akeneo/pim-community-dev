<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Doctrine\Common\Inflector\Inflector;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityExtendBundle\Tools\Generator;

class AbstractDatagrid extends DatagridManager
{
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
     * @param FieldDescriptionCollection $fieldsCollection
     * @param FieldConfigId $field
     * @param Config $fieldConfig
     */
    public function addDynamicField(
        FieldDescriptionCollection $fieldsCollection,
        FieldConfigId $field,
        Config $fieldConfig
    ) {
        $fieldObject = new FieldDescription();
        $fieldObject->setName(Generator::PREFIX . $field->getFieldName());
        $fieldObject->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $fieldConfig->get('label') ? : $field->getFieldName(),
                'field_name'  => Generator::PREFIX . $field->getFieldName(),
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );

        $fieldsCollection->add($fieldObject);
    }

    public function addDynamicFields()
    {
        $entityProvider = $this->configManager->getProvider('entity');

        /** @var FieldConfigId $field */
        $fields = $entityProvider->getIds($this->entityName);
        foreach ($fields as $field) {
            $extendProvider = $this->configManager->getProvider('extend');
            $fieldName      = $field->getFieldName();

            if ($extendProvider->getConfig($this->entityName, $fieldName)->get('extend')) {
                /** @var Config $datagridConfig */
                $datagridConfig = $this->configManager->getProvider('datagrid')->getConfig(
                    $this->entityName,
                    $fieldName
                );
                if ($datagridConfig->get('is_visible')) {
                    /** @var Config $fieldConfig */
                    $fieldConfig = $entityProvider->getConfig($this->entityName, $fieldName);

                    $this->addDynamicField($this->getFieldDescriptionCollection(), $field, $fieldConfig);
                }
            }
        }
    }
}
