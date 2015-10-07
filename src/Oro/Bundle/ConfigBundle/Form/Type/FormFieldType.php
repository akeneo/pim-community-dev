<?php

namespace Oro\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\ConfigBundle\Config\Tree\FieldNodeDefinition;

class FormFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'target_field'       => array(
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
        $useParentOptions = array('required' => false, 'label' => 'Default');
        $builder->add('use_parent_scope_value', 'checkbox', $useParentOptions);

        if ($options['target_field'] instanceof FieldNodeDefinition) {
            $filedOptions = $options['target_field']->getOptions();
            unset($filedOptions['block']);
            unset($filedOptions['subblock']);
            $options['target_field'] = array(
                'type'    => $options['target_field']->getType(),
                'options' => $filedOptions
            );
        }
        $builder->add('value', $options['target_field']['type'], $options['target_field']['options']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_config_form_field_type';
    }
}
