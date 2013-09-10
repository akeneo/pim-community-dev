<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PhoneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add(
                'phone',
                'text',
                array(
                    'label' => 'Phone',
                    'required' => true
                )
            )
            ->add(
                'primary',
                'radio',
                array(
                    'label' => 'Primary',
                    'required' => false
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_phone';
    }
}
