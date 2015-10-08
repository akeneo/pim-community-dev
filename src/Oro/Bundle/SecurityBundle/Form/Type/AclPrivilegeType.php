<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeIdentityType;
use Oro\Bundle\SecurityBundle\Form\Type\AclPermissionType;

class AclPrivilegeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'identity',
            new AclPrivilegeIdentityType(),
            array(
                'required' => false,
            )
        );

        $builder->add(
            'permissions',
            new PermissionCollectionType(),
            array(
                'type' => new AclPermissionType(),
                'allow_add' => true,
                'prototype' => false,
                'allow_delete' => false,
                'options' => array(
                    'privileges_config' => $options['privileges_config']
                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['privileges_config'] = $options['privileges_config'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'privileges_config' => array(),
                'data_class' => 'Oro\Bundle\SecurityBundle\Model\AclPrivilege',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_privilege';
    }
}
