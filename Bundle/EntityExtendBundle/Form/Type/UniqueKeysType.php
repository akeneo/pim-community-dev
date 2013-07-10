<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;

class UniqueKeysType extends AbstractType
{
    /**
     * @var FieldConfig[]
     */
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array_map(function (FieldConfig $field) {
            return ucfirst($field->getCode());
        }, $this->fields);

        $builder->add(
            'keys',
            'collection',
            array(
                'type'           => 'choice',
                'options'        => array(
                    'multiple'    => true,
                    'choices'     => $choices,
                    //'empty_value' => false
                ),
                'allow_add'      => true,
                'allow_delete'   => true,
                'prototype'      => true,
                'prototype_name' => 'tag__name__',
                'label'          => ' '
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_unique_keys_type';
    }
}
