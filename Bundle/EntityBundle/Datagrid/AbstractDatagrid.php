<?php

namespace Oro\Bundle\EntityBundle\Datagrid;

use Doctrine\Common\Inflector\Inflector;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
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
     * @param FieldConfigIdInterface     $field
     * @param Config                     $fieldConfig
     */
    public function addDynamicField(
        FieldDescriptionCollection $fieldsCollection,
        FieldConfigIdInterface $field,
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
        $entityProvider   = $this->configManager->getProvider('entity');
        $extendProvider   = $this->configManager->getProvider('extend');
        $datagridProvider = $this->configManager->getProvider('datagrid');

        $fieldIds = $entityProvider->getIds($this->entityName);
        foreach ($fieldIds as $fieldId) {
            if ($extendProvider->getConfigById($fieldId)->is('owner', ExtendManager::OWNER_CUSTOM)) {
                if ($datagridProvider->getConfigById($fieldId)->is('is_visible')) {
                    $fieldConfig = $entityProvider->getConfigById($fieldId);

                    $this->addDynamicField($this->getFieldDescriptionCollection(), $fieldId, $fieldConfig);
                }
            }
        }
    }
}
