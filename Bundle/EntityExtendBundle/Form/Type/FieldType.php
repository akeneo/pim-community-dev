<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldType extends AbstractType
{
    protected $types = array(
        'string',
        'integer',
        'smallint',
        'bigint',
        'boolean',
        'decimal',
        'date',
        'time',
        'datetime',
        'text',
        'float',
    );

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text', array(
            'label' => 'Field Name',
            'block' => 'type',
        ));
        $builder->add('type', 'choice', array(
            'choices'     => array_combine(array_values($this->types), $this->types),
            'empty_value' => 'Please choice type...',
            'block'       => 'type',
        ));

        $builder->add('options', 'oro_entity_config_config_field_type', array(
            'class_name' => $options['class_name'],
            'field_id'   => '',
            'field_name' => '',
            'field_type' => '',
            'block'      => 'options',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class_name'   => '',
            'block_config' => array(
                'type' => array(
                    'title'    => 'Doctrine Type',
                    'priority' => 1,
                )
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_field_type';
    }
}
