<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            [
                'required'    => false,
                'label'       => 'Current password',
                'constraints' => [
                    new UserPassword()
                ],
                'mapped' => false,
            ]
        )
        ->add(
            'plainPassword',
            'repeated',
            [
                'required'        => true,
                'type'            => 'password',
                'invalid_message' => 'The password fields must match.',
                'options'         => [
                    'attr' => [
                        'class' => 'password-field'
                    ]
                ],
                'first_options'      => ['label' => 'New password'],
                'second_options'     => ['label' => 'Repeat new password'],
                'mapped'             => false,
                'cascade_validation' => true,
            ]
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'inherit_data'       => true,
                'cascade_validation' => true,
            ]
        );
    }
}
