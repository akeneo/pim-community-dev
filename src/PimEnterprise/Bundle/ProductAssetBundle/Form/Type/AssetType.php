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

use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Pim\Bundle\UIBundle\Form\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    /** @var string */
    protected $categoryClass;

    /**
     * @param string $entityClass
     * @param string $tagClass
     * @param string $categoryClass
     */
    public function __construct($entityClass, $tagClass, $categoryClass)
    {
        $this->entityClass = $entityClass;
        $this->tagClass = $tagClass;
        $this->categoryClass = $categoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'code',
            TextType::class,
            [
                'read_only' => true,
                'label'     => 'pimee_product_asset.enrich_asset.view.code',
            ]
        );
        $builder->add(
            'description',
            TextareaType::class,
            [
                'required' => false,
                'label'    => 'pimee_product_asset.enrich_asset.view.description',
            ]
        );
        $builder->add(
            'tags',
            AjaxAssetTagType::class,
            [
                'class'        => $this->tagClass,
                'multiple'     => true,
                'is_creatable' => true,
                'label'        => 'pimee_product_asset.enrich_asset.view.tag',
            ]
        );
        $builder->add(
            'endOfUseAt',
            DateType::class,
            [
                'required' => false,
                'label'    => 'pimee_product_asset.enrich_asset.view.end_of_use',
            ]
        );
        $builder->add(
            'references',
            CollectionType::class,
            ['entry_type' => ReferenceType::class]
        );
        $builder->add(
            'categories',
            EntityIdentifierType::class,
            [
                'class'    => $this->categoryClass,
                'required' => true,
                'mapped'   => true,
                'multiple' => true,
            ]
        );
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
    public function getBlockPrefix()
    {
        return 'pimee_product_asset';
    }
}
