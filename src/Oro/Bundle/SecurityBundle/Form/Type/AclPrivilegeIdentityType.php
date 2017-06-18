<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclPrivilegeIdentityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'id',
            HiddenType::class,
            [
                'required' => true,
            ]
        );
        $builder->add(
            'name',
            ObjectLabelType::class,
            [
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_acl_privilege_identity';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => AclPrivilegeIdentity::class,
            ]
        );
    }
}
