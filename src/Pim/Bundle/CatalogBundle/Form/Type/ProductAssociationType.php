<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for ProductAssociation
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'associationType',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\AssociationType',
                    'property' => 'id',
                    'multiple' => false
                )
            )
            ->add(
                'appendProducts',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Model\Product',
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'removeProducts',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Model\Product',
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'appendGroups',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\Group',
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            )
            ->add(
                'removeGroups',
                'oro_entity_identifier',
                array(
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\Group',
                    'mapped'   => false,
                    'required' => false,
                    'multiple' => true
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\ProductAssociation'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_product_association';
    }
}
