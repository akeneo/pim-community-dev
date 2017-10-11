<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Valid;

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
            PasswordType::class,
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
            RepeatedType::class,
            [
                'required'        => true,
                'type'            => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options'         => [
                    'attr' => [
                        'class' => 'password-field'
                    ]
                ],
                'first_options'      => ['label' => 'New password'],
                'second_options'     => ['label' => 'Repeat new password'],
                'mapped'             => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
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
                'constraints' => new Valid(),
            ]
        );
    }
}
