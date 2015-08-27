<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Type;

use PimEnterprise\Bundle\EnrichBundle\Form\Type\CategoryPermissionsType as BaseCategoryPermissionsType;
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
            'pimee_security_groups',
            [
                'label' => 'pimee_product_asset.category.permissions.view.label',
                'help' => 'pimee_product_asset.category.permissions.view.help'
            ]
        );
        $builder->add(
            'edit',
            'pimee_security_groups',
            [
                'label' => 'pimee_product_asset.category.permissions.edit.label',
                'help' => 'pimee_product_asset.category.permissions.edit.help'
            ]
        );
        $builder->add(
            'apply_on_children',
            'checkbox',
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
    public function getName()
    {
        return 'pimee_product_asset_category_permissions';
    }
}
