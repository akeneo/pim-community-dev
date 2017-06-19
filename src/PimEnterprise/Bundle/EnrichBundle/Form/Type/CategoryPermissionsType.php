<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use PimEnterprise\Bundle\SecurityBundle\Form\Type\GroupsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Category permissions
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryPermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'view',
            GroupsType::class,
            ['label' => 'category.permissions.view.label', 'help' => 'category.permissions.view.help']
        );
        $builder->add(
            'edit',
            GroupsType::class,
            ['label' => 'category.permissions.edit.label', 'help' => 'category.permissions.edit.help']
        );
        $builder->add(
            'own',
            GroupsType::class,
            ['label' => 'category.permissions.own.label', 'help' => 'category.permissions.own.help']
        );
        $builder->add(
            'apply_on_children',
            CheckboxType::class,
            [
                'label'    => 'category.permissions.apply_on_children.label',
                'help'     => 'category.permissions.apply_on_children.help',
                'data'     => true,
                'required' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['mapped' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pimee_enrich_category_permissions';
    }
}
