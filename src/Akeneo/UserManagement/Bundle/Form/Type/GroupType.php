<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * Roles was commended due a task BAP-1675
         */
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                ]
            )
            /*->add(
                'roles',
                'entity',
                array(
                    'label'    => 'Roles',
                    'class'    => 'PimUserBundle:Role',
                    'query_builder' => function ($builder) {
                        return $builder->createQueryBuilder('r')
                            ->where('r.role != :anonRole')
                            ->setParameter('anonRole', User::ROLE_ANONYMOUS);
                    },
                    'property' => 'label',
                    'required' => true,
                    'multiple' => true,
                )
            )*/
            ->add(
                'appendUsers',
                EntityIdentifierType::class,
                [
                    'class'    => User::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeUsers',
                EntityIdentifierType::class,
                [
                    'class'    => User::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
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
                'data_class' => Group::class,
                'intention'  => 'group',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_user_group';
    }
}
