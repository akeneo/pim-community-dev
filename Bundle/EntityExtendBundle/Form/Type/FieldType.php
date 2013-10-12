<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldType extends AbstractType
{
    protected $types = array(
        'string'     => 'String',
        'integer'    => 'Integer',
        'smallint'   => 'SmallInt',
        'bigint'     => 'BigInt',
        'boolean'    => 'Boolean',
        'decimal'    => 'Decimal',
        'date'       => 'Date',
        'text'       => 'Text',
        'float'      => 'Float',
        'oneToMany'  => 'Relation one to many',
        'manyToOne'  => 'Relation many to one',
        //'manyToMany' => 'Relation many to many',
    );

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'fieldName',
            'text',
            array(
                'label' => 'Field Name',
                'block' => 'type',
            )
        );
        $builder->add(
            'type',
            'choice',
            array(
                'choices'     => $this->types,
                'empty_value' => 'Please choice type...',
                'block'       => 'type',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'require_js'   => array(),
                'block_config' => array(
                    'type' => array(
                        'title'    => 'General',
                        'priority' => 1,
                    )
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_field_type';
    }
}
