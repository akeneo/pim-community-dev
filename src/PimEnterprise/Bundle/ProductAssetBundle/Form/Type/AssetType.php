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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Asset type
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetType extends AbstractType
{
    /** @var string */
    protected $tagClass;

    /** @var string */
    protected $entityClass;

    /**
     * @param string $entityClass
     * @param string $tagClass
     */
    public function __construct($entityClass, $tagClass)
    {
        $this->entityClass = $entityClass;
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
                'class'        => $this->tagClass,
                'multiple'     => true,
                'is_creatable' => true,
            ]
        );
        $builder->add('endOfUseAt', 'oro_date', ['required' => false]);
        $builder->add('references', 'collection', ['type' => 'pimee_product_asset_reference']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->entityClass,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_product_asset';
    }
}
