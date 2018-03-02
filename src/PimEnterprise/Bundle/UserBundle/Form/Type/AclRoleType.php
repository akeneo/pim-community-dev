<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Pim\Component\User\Model\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Override from Pim\UserBundle to use the User override class in the EE.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 *
 * @deprecated To be removed when UserBundle from oro will be moved to Pim namespace
 */
class AclRoleType extends AbstractType
{
    /** @var array privilege fields config */
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
        foreach ($options['privilegeConfigOption'] as $fieldName => $config) {
            $builder->add($fieldName, PrivilegeCollectionType::class, [
                'entry_type'    => AclPrivilegeType::class,
                'allow_add'     => true,
                'prototype'     => false,
                'allow_delete'  => false,
                'mapped'        => false,
                'entry_options' => [
                    'privileges_config' => $config,
                ],
            ]);
        }

        $builder->add('label', TextType::class, [
            'label' => 'Role'
        ]);

        $builder->add('appendUsers', EntityIdentifierType::class, [
            'class'    => 'PimEnterpriseUserBundle:User',
            'required' => false,
            'mapped'   => false,
            'multiple' => true,
        ]);

        $builder->add('removeUsers', EntityIdentifierType::class, [
            'class'    => 'PimEnterpriseUserBundle:User',
            'required' => false,
            'mapped'   => false,
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'            => Role::class,
            'intention'             => 'role',
            'privilegeConfigOption' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_user_role_form';
    }
}
