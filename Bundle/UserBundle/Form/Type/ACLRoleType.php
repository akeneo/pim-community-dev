<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\SecurityBundle\Form\Type\EntityRowType;

use Symfony\Component\Form\AbstractType;

class ACLRoleType extends AbstractType
{
    /**
     * @var array Array with Entities ACL row fields
     */
    protected $entitiesFieldsConfig = array(
        'oid' => array(
            'type' => 'oro_acl_object_name',
            'label' => 'Entity',
            'need_check' => false
        )
    );


    public function __construct($permissions)
    {
        foreach ($permissions as $permission) {
            $this->entitiesFieldsConfig[$permission] = array(
                    'type' => 'checkbox',
                    'label' => $permission,
                    'need_check' => true
            );
        }
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
        )
        ->add(
            'label',
            'text',
            array(
                'required' => false,
            )
        );
        $builder->add('entities', 'oro_acl_collection', array(
            'type'   => new EntityRowType(),
            'allow_add' => true,
            'prototype' => false,
            'allow_delete' => false,
            'mapped' => false,
            'options'  => array(
                'fields_config' => $this->entitiesFieldsConfig,
            )

        ));
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

    public function getName()
    {
        return 'oro_testtype';
    }
}
