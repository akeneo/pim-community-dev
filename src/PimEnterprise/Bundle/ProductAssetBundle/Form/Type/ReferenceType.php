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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Reference form type
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceType extends AbstractType
{
    /** @var string */
    protected $entityClass;

    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'akeneo_file_storage_file', ['required' => false]);
        $builder->add('variations', 'collection', array('type' => 'pimee_product_asset_variation'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return 'pimee_product_asset_reference';
    }
}
