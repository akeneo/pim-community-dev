<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Form\Type;

use Akeneo\Pim\Permission\Bundle\Form\Type\CategoryPermissionsType as BaseCategoryPermissionsType;
use Akeneo\Pim\Permission\Bundle\Form\Type\GroupsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for Asset Category permissions
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class CategoryPermissionsType extends BaseCategoryPermissionsType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'view',
            GroupsType::class,
            [
                'label' => 'pimee_product_asset.category.permissions.view.label',
                'help'  => 'pimee_product_asset.category.permissions.view.help'
            ]
        );
        $builder->add(
            'edit',
            GroupsType::class,
            [
                'label' => 'pimee_product_asset.category.permissions.edit.label',
                'help'  => 'pimee_product_asset.category.permissions.edit.help'
            ]
        );
        $builder->add(
            'apply_on_children',
            CheckboxType::class,
            [
                'label'    => 'pimee_product_asset.category.permissions.apply_on_children.label',
                'help'     => 'pimee_product_asset.category.permissions.apply_on_children.help',
                'data'     => true,
                'required' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pimee_product_asset_category_permissions';
    }
}
