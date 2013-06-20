<?php

namespace Oro\Bundle\EntityExtendBundle\Config;

use Oro\Bundle\EntityConfigBundle\Provider\AbstractConfigProvider;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class ExtendConfigProvider extends AbstractConfigProvider
{
    public $fields_config = array(
        'config' => array(
            'is_extend' => array(
                'grid'=> array(
                    'show' => true,
                    'type' => FieldDescriptionInterface::TYPE_BOOLEAN,
                    'is_sortable' => true,
                    'is_filtrable' => false,
                ),
                'form_type' => array(
                    'type' => 'choice',
                    'choices' => array('no','yes'),
                )
            ),
        ),
        'grid_actions' => array(
            'remove' => array(),
        ),
        'view_actions' => array(
            'remove' => array(),
        ),
    );

    public $entity_config = array(
        'config' => array(
            'is_extend' => array(
                'grid'=> array(
                    'show' => true,
                    'type' => FieldDescriptionInterface::TYPE_BOOLEAN,
                    'is_sortable' => true,
                    'is_filtrable' => false,
                ),
                'form_type' => array(
                    'type' => 'choice',
                    'choices' => array('no','yes'),
                )
            ),
        ),
        'extend_class' => array(
            'required' => true,
        ),
        'proxy_class' => array(
            'required' => true,
        ),
    );


    public function isExtend($entityName)
    {
        if (!$this->hasConfig($entityName)) {
            return false;
        }

        return $this->getConfig($entityName)->has('is_extend');
    }

    public function getExtendClass($entityName)
    {
        return $this->getConfig($entityName)->get('extend_class', true);
    }

    public function getProxyClass($entityName)
    {
        return $this->getConfig($entityName)->get('proxy_class', true);
    }

    public function getScope()
    {
        return 'extend';
    }
}