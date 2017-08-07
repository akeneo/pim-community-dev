<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclRoleType extends AbstractType
{
    /**
     * @var array privilege fields config
     */
    protected $privilegeConfig;

    /**
     * @param array $privilegeTypeConfig
     */
    public function __construct(array $privilegeTypeConfig)
    {
        $this->privilegeConfig = $privilegeTypeConfig;
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

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add(
                $fieldName,
                new PrivilegeCollectionType(),
                [
                    'entry_type'   => new AclPrivilegeType(),
                    'allow_add'    => true,
                    'prototype'    => false,
                    'allow_delete' => false,
                    'mapped'       => false,
                    'options'      => [
                        'privileges_config' => $config,
                    ]
                ]
            );
        }

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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'            => Role::class,
                'intention'             => 'role',
                'privilegeConfigOption' => [],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_user_role_form';
    }
}
