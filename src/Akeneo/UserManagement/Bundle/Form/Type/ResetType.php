<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ResetType extends AbstractType
{
    private const PASSWORD_MINIMUM_LENGTH = 8;
    private const PASSWORD_MAXIMUM_LENGTH = 64;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class User entity class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type'            => PasswordType::class,
                'required'        => true,
                'first_options'   => ['label' => 'Password'],
                'second_options'  => ['label' => 'Again'],
                'constraints' => [
                    new Length([
                        'min' => self::PASSWORD_MINIMUM_LENGTH,
                        'max' => self::PASSWORD_MAXIMUM_LENGTH
                    ])
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->class,
                'intention'  => 'reset',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_user_reset';
    }
}
