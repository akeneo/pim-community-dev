<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleApiType extends AclRoleType
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'label',
            'text',
            [
                'required' => true,
                'label'    => 'Role'
            ]
        );

        $builder->add(
            'appendUsers',
            'oro_entity_identifier',
            [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]
        );

        $builder->add(
            'removeUsers',
            'oro_entity_identifier',
            [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
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
        parent::configureOptions($resolver);

        $resolver->setDefaults(['csrf_protection' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'role';
    }
}
