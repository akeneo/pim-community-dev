<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add(
                'email',
                'email',
                array(
                    'label' => 'Email',
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
        return 'oro_email';
    }
}
