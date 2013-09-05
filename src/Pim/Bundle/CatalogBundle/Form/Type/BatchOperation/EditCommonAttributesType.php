<?php

namespace Pim\Bundle\CatalogBundle\Form\Type\BatchOperation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type of the EditCommonAttributes operation
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesType extends AbstractType
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
                    'type' => 'pim_product_value'
                )
            )
            ->add(
                'locale',
                'choice',
                array(
                    'choices' => $options['locales']
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
                'data_class' => 'Pim\\Bundle\\CatalogBundle\\BatchOperation\\EditCommonAttributes',
                'locales' => array(),
            )
        );
    }

    public function getName()
    {
        return 'pim_catalog_operation_edit_common_attributes';
    }
}
