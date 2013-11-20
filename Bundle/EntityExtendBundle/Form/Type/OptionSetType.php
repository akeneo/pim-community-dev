<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionSetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('priority', 'hidden')
            ->add(
                'label',
                'text',
                [
                    'label' => 'Value',
                    'required' => true,
                ]
            )
            ->add(
                'default',
                'radio',
                [
                    'label' => 'Default',
                    'required' => false,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    /*public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\EntityExtendBundle\Entity\OptionSet',
            ]
        );
    }*/

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_option_set';
    }
}
