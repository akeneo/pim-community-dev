<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclPermissionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'accessLevel',
            $options['privileges_config']['field_type'],
            [
                'required' => false,
            ]
        );
        $builder->add(
            'name',
            'hidden',
            [
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_permission';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => 'Oro\Bundle\SecurityBundle\Model\AclPermission',
                'privileges_config' => []
            ]
        );
    }
}
