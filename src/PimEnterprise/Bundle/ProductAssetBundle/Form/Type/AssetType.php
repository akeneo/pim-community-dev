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
 * Asset type
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetType extends AbstractType
{
    /** @var string */
    protected $tagClass;

    /**
     * @params string $tagClass
     */
    public function __construct($tagClass)
    {
        $this->tagClass = $tagClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text', ['read_only' => true]);
        $builder->add('description', 'textarea', ['required' => false]);
        $builder->add(
            'tags',
            'pim_ajax_asset_tag',
            [
                'class'    => $this->tagClass,
                'multiple' => true,
                'attr'     => ['data-tags' => '']
            ]
        );
        $builder->add('endOfUseAt', 'oro_date', ['required' => false]);

        $builder->add('references', 'collection', array('type' => 'pimee_product_asset_reference'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_product_asset';
    }
}
