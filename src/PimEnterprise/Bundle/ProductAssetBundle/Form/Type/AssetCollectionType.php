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

use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Asset collection type
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetCollectionType extends AbstractType
{
    /** @var DataTransformerInterface */
    protected $assetTransformer;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param DataTransformerInterface $assetTransformer
     * @param UserContext              $userContext
     */
    public function __construct(DataTransformerInterface $assetTransformer, UserContext $userContext)
    {
        $this->assetTransformer = $assetTransformer;
        $this->userContext      = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->assetTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'attr'       => [
                'locale' => $this->userContext->getCurrentLocale()->getCode(),
                'scope'  => $this->userContext->getUserChannel()->getCode()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_assets_collection';
    }
}
