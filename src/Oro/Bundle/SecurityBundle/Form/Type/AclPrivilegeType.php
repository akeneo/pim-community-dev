<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            [
                'required' => false,
            ]
        );

        $builder->add(
            'permissions',
            new PermissionCollectionType(),
            [
                'type'         => new AclPermissionType(),
                'allow_add'    => true,
                'prototype'    => false,
                'allow_delete' => false,
                'options'      => [
                    'privileges_config' => $options['privileges_config']
                ],
            ]
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
            [
                'privileges_config' => [],
                'data_class'        => AclPrivilege::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_acl_privilege';
    }
}
