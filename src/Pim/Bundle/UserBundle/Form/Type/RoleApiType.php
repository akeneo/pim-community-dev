<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Pim\Bundle\UserBundle\Form\Subscriber\PatchSubscriber;
use Pim\Component\User\Model\Role;
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
                'label' => 'Role',
            ]
        );

        $builder->add(
            'appendUsers',
            EntityIdentifierType::class,
            [
                'class' => 'PimUserBundle:User',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
            ]
        );

        $builder->add(
            'removeUsers',
            EntityIdentifierType::class,
            [
                'class' => 'PimUserBundle:User',
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
