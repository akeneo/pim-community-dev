<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType;
use Akeneo\UserManagement\Bundle\Form\Subscriber\PatchSubscriber;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleApiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'label',
            TextType::class,
            [
                'required' => true,
                'label' => 'pim_user.roles.title',
            ]
        );

        $builder->add(
            'appendUsers',
            EntityIdentifierType::class,
            [
                'class' => User::class,
                'required' => false,
                'mapped' => false,
                'multiple' => true,
            ]
        );

        $builder->add(
            'removeUsers',
            EntityIdentifierType::class,
            [
                'class' => User::class,
                'required' => false,
                'mapped' => false,
                'multiple' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Role::class,
                'intention' => 'role',
                'privilegeConfigOption' => [],
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'role';
    }
}
