<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class AddTagsToAssetsType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $formName;

    /** @var string */
    protected $tagClass;

    /**
     * @param string $dataClass
     * @param string $formName
     * @param string $tagClass
     */
    public function __construct($dataClass, $formName, $tagClass)
    {
        $this->dataClass = $dataClass;
        $this->formName  = $formName;
        $this->tagClass  = $tagClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'tags',
            'pim_ajax_asset_tag',
            [
                'class'        => $this->tagClass,
                'multiple'     => true,
                'is_creatable' => true,
                'label'        => 'pimee_product_asset.enrich_asset.view.tag',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->dataClass]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->formName;
    }
}
