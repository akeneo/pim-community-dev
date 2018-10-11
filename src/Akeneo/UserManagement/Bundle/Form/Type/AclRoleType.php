<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AclRoleType to remove ACLs for disabled locales
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleType extends AbstractType
{
    /**
     * @var array privilege fields config
     */
    private $privilegeConfig;

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
        foreach ($options['privilegeConfigOption'] as $fieldName => $config) {
            $builder->add(
                $fieldName,
                PrivilegeCollectionType::class,
                [
                    'entry_type' => AclPrivilegeType::class,
                    'allow_add' => true,
                    'prototype' => false,
                    'allow_delete' => false,
                    'mapped' => false,
                    'entry_options' => [
                        'privileges_config' => $config,
                    ],
                ]
            );
        }

        // Empty the privilege config to prevent parent from overriding the fields
        $this->privilegeConfig = [];

        $builder->add(
            'label',
            TextType::class,
            [
                'required' => true,
                'label' => 'pim_user.roles.title',
            ]
        );

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add(
                $fieldName,
                new PrivilegeCollectionType(),
                [
                    'entry_type' => new AclPrivilegeType(),
                    'allow_add' => true,
                    'prototype' => false,
                    'allow_delete' => false,
                    'mapped' => false,
                    'options' => [
                        'privileges_config' => $config,
                    ],
                ]
            );
        }

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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Role::class,
                'intention' => 'role',
                'privilegeConfigOption' => [],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_user_role_form';
    }
}
