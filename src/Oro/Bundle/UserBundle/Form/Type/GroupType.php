<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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
                'text',
                [
                    'required' => true,
                ]
            )
            /*->add(
                'roles',
                'entity',
                array(
                    'label'    => 'Roles',
                    'class'    => 'OroUserBundle:Role',
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
                'pim_enrich_entity_identifier',
                [
                    'class'    => 'PimUserBundle:User',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeUsers',
                'pim_enrich_entity_identifier',
                [
                    'class'    => 'PimUserBundle:User',
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
                'data_class' => 'Oro\Bundle\UserBundle\Entity\Group',
                'intention'  => 'group',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_group';
    }
}
