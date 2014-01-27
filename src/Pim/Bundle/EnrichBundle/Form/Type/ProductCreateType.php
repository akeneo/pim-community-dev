<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Product creation form type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'values',
                'collection',
                array(
                    'type'               => 'pim_product_value',
                    'allow_add'          => true,
                    'allow_delete'       => true,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                )
            )
            ->add(
                'family',
                'entity',
                array(
                    'class' => 'Pim\Bundle\CatalogBundle\Entity\Family',
                    'empty_value' => "",
                    'select2' => true,
                    'attr'    => array(
                        'data-placeholder' => 'Choose a family'
                    )
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_create';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_product';
    }
}
