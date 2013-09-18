<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordType extends AbstractType
{
    /**
     * @var ChangePasswordSubscriber
     */
    protected $subscriber;

    /**
     * @param ChangePasswordSubscriber $subscriber
     */
    public function __construct(ChangePasswordSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder->add(
            'currentPassword',
            'password',
            array(
                'required' => false,
                'label' => 'Current password',
                'constraints' => array(
                    new UserPassword()
                ),
                'mapped' => false,
                'auto_initialize' => false,
            )
        )
        ->add(
            'plainPassword',
            'repeated',
            array(
                'required' => true,
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array(
                    'attr' => array(
                        'class' => 'password-field'
                    )
                ),
                'first_options'  => array('label' => 'New password'),
                'second_options' => array('label' => 'Repeat new password'),
                'mapped' => false,
                'cascade_validation' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_change_password';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'inherit_data' => true,
                'cascade_validation' => true,
            )
        );
    }
}
