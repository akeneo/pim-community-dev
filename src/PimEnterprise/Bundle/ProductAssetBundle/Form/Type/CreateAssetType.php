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
    /** @var ReferenceType */
    protected $referenceType;

    /**
     * @param ReferenceType $referenceType
     */
    public function __construct(ReferenceType $referenceType)
    {
        $this->referenceType = $referenceType;
    }

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
        $builder->add('reference', $this->referenceType, ['required' => false]);
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
