<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;
use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            TextType::class,
            [
                'required' => true,
                'label'    => 'Role'
            ]
        );

        $builder->add(
            'appendUsers',
            EntityIdentifierType::class,
            [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]
        );

        $builder->add(
            'removeUsers',
            EntityIdentifierType::class,
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
    public function getBlockPrefix()
    {
        return 'role';
    }
}
