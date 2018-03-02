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

use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Pim\Component\User\Model\Group;
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
class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('appendUsers', EntityIdentifierType::class, [
                'class'    => 'PimEnterpriseUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ])
            ->add('removeUsers', EntityIdentifierType::class, [
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
            'data_class' => Group::class,
            'intention'  => 'group',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_user_group';
    }
}
