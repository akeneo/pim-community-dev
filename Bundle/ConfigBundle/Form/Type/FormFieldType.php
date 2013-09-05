<?php

namespace Oro\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormFieldType extends AbstractType
{
    /**
     * Pass target field options to field form type
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'target_field' => array(
                    'type'    => 'text',
                    'options' => array()
                ),
                'cascade_validation' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'use_parent_scope_value',
            'checkbox',
            array(
                'required' => false,
                'label'    => 'Default'
            )
        );
        $builder->add(
            'value',
            $options['target_field']['type'],
            $options['target_field']['options']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_config_form_field_type';
    }
}
