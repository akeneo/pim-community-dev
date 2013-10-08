<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Pim\Bundle\CatalogBundle\Entity\Repository\ProductAttributeRepository;
use Pim\Bundle\CatalogBundle\Form\Subscriber\VariantGroupSubscriber;

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
        $builder->add('code');

        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\VariantGroupTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\VariantGroup',
                'property_path'     => 'translations'
            )
        );

        $builder->add(
            'attributes',
            'entity',
            array(
                'label'    => 'Axis',
                'required' => true,
                'multiple' => true,
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
                'query_builder' => function (ProductAttributeRepository $repository) {
                    return $repository->findAllAxisQB();
                }
            )
        );

        $builder->add(
            'appendProducts',
            'oro_entity_identifier',
            array(
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Product',
                'required' => false,
                'mapped'   => false,
                'multiple' => true
            )
        );

        $builder->add(
            'removeProducts',
            'oro_entity_identifier',
            array(
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Product',
                'required' => false,
                'mapped'   => false,
                'multiple' => true
            )
        );

        $builder->addEventSubscriber(new VariantGroupSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\VariantGroup'
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
