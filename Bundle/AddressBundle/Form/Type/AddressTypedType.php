<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressTypedType extends AddressType
{
    const TYPE_SHIPPING = 1;
    const TYPE_BILLING = 2;
    const TYPE_OTHER = 3;

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->add(
            'type',
            'choice',
            array(
                'choices' => $this->getChoices(),
                'required' => false,
                'empty_value' => 'Choose type...',
            )
        );
        parent::addEntityFields($builder);
    }

    protected function getChoices()
    {
        return array(
            self::TYPE_SHIPPING => 'Shipping address',
            self::TYPE_BILLING => 'Billing address',
            self::TYPE_OTHER => 'Other'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_typed';
    }
}
