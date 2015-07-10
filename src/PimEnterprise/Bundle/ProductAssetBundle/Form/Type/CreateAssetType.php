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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for asset creation
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CreateAssetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'isLocalized',
            'switch',
            [
                'mapped' => false,
                'label'  => 'pimee_product_asset.popin.create.is_localized'
            ]
        );
        $builder->add('reference_file', 'akeneo_file_storage_file');
        $builder->add('code', 'text', ['required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_product_asset_create';
    }
}
