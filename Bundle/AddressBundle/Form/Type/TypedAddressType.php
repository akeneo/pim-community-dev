<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TypedAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'types',
                'translatable_entity',
                array(
                    'class'    => 'OroAddressBundle:AddressType',
                    'property' => 'label',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                )
            )
            ->add(
                'primary',
                'checkbox',
                array(
                    'label' => 'Primary',
                    'required' => false
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_address';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_typed_address';
    }
}
