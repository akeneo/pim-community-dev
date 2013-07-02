<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class AddressTypedType extends AddressType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->add(
            'types',
            'entity',
            array(
                'class' => 'OroAddressBundle:AddressType',
                'property' => 'label',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            )
        );
        $builder->add(
            'primary',
            'checkbox',
            array(
                'label' => 'Primary',
                'required' => false
            )
        );
        parent::addEntityFields($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_typed';
    }
}
