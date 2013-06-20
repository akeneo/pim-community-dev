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
            'type',
            'entity',
            array(
                'class' => 'OroAddressBundle:AddressType',
                'property' => 'type',
                'required' => false,
                'empty_value' => 'Choose type...'
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
