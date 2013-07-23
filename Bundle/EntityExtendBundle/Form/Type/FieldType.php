<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldType extends AbstractType
{
    protected $types = array(
        'string',
        'text',
        'integer',
        'float',
        'decimal',
        'boolean',
        'datetime',
        'date'
    );

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text', array(
            'label'    => 'Field Name',
            'block'    => 'type',
            'subblock' => 'common',
        ));
        $builder->add('type', 'choice', array(
            'choices'     => array_combine(array_values($this->types), $this->types),
            'empty_value' => false,
            'block'       => 'type',
            'subblock'    => 'common',
        ));
        $builder->add('length', 'integer', array(
            'block'    => 'type',
            'subblock' => 'custom',
            'required' => false,
        ));
        $builder->add('unique', 'checkbox', array(
            'block'    => 'type',
            'subblock' => 'common',
            'required' => false,
        ));
        $builder->add('nullable', 'checkbox', array(
            'block'    => 'type',
            'subblock' => 'common',
            'required' => false,
        ));
        $builder->add('precision', 'integer', array(
            'block'    => 'type',
            'subblock' => 'custom',
            'required' => false,
        ));
        $builder->add('scale', 'integer', array(
            'block'    => 'type',
            'subblock' => 'custom',
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'block_config' => array(
                'type' => array(
                    'title'     => 'Doctrine Type',
                    'priority'  => 1,
                    'subblocks' => array(
                        'common' => array(
                            'title'    => 'Common Setting',
                            'priority' => 2,
                        ),
                        'custom' => array(
                            'title'    => 'Custom Setting',
                            'priority' => 1,
                        ),
                    )
                ),
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
