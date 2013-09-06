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
            'role',
            'text',
            array(
                'required' => true,
            )
        );
        $builder->add(
            'label',
            'text',
            array(
                'required' => false,
            )
        );

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add($fieldName, new PrivilegeCollectionType(), array(
                'type' => new AclPrivilegeType(),
                'allow_add' => true,
                'prototype' => false,
                'allow_delete' => false,
                'mapped' => false,
                'options' => array(
                    'privileges_config' => $config,
                )
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\UserBundle\Entity\Role',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_role';
    }
}
