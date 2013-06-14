<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
