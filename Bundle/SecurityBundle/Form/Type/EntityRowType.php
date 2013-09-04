<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Oro\Bundle\SecurityBundle\Form\EventListener\EntityRowSubscriber;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class EntityRowType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['fields_config'] as $fieldName => $fieldConfig ) {
            $builder->add($fieldName, $fieldConfig['type'], array(
                'required' => false,
                'label' => $fieldConfig['label']
            ));
        }

        $builder->addEventSubscriber(new EntityRowSubscriber($options['fields_config']));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields_config'] = $options['fields_config'];
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'fields_config' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_entity_row';
    }
}