<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Pim\Bundle\CatalogBundle\Entity\Repository\ProductAttributeRepository;

/**
 * Type for variant group form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add(
                'label',
                'pim_translatable_field',
                array(
                    'field'             => 'label',
                    'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\VariantGroupTranslation',
                    'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\VariantGroup',
                    'property_path'     => 'translations'
                )
            )
            ->add(
                'axis',
                'entity',
                array(
                    'required' => true,
                    'multiple' => true,
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
                    'query_builder' => function (ProductAttributeRepository $repository) {
                        return $repository->findAxisQB();
                    }
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_variant_group';
    }
}
