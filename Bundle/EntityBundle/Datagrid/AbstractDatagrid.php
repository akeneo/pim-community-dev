<?php

namespace Oro\Bundle\EntityBundle\Datagrid;


use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

class AbstractDatagrid extends DatagridManager
{
    /** @var ConfigManager  */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function addDynamicFields(FieldDescriptionCollection $fieldsCollection)
    {
        var_dump('in action');
        //var_dump($this->getDatagrid()->getEntityName());

        var_dump($this->entityName);
        $fields = array();
        foreach ($this->configManager->getProviders() as $provider) {
            foreach ($provider->getPropertyConfig()->getItems() as $code => $item) {
                if (isset($item['grid'])) {
                    $item['grid'] = $provider->getPropertyConfig()->initConfig($item['grid']);

                    $fieldObject = new FieldDescription();
                    $fieldObject->setName($code);
                    $fieldObject->setOptions(
                        array_merge(
                            $item['grid'],
                            array(
                                'expression' => 'cev' . $code . '.value',
                                'field_name' => $code,
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
                }
            }
        }

        ksort($fields);
        foreach ($fields as $field) {
            $fieldsCollection->add($field);
        }
    }
}
