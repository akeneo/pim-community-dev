<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;

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
            'text',
            array(
                'required' => true,
                'label' => 'Role'
            )
        );

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add(
                $fieldName,
                new PrivilegeCollectionType(),
                array(
                    'type' => new AclPrivilegeType(),
                    'allow_add' => true,
                    'prototype' => false,
                    'allow_delete' => false,
                    'mapped' => false,
                    'options' => array(
                        'privileges_config' => $config,
                    )
                )
            );
        }

        $builder->add(
            'appendUsers',
            'oro_entity_identifier',
            array(
                'class'    => 'OroUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );

        $builder->add(
            'removeUsers',
            'oro_entity_identifier',
            array(
                'class'    => 'OroUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\UserBundle\Entity\Role',
                'intention'  => 'role',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_role_form';
    }
}
