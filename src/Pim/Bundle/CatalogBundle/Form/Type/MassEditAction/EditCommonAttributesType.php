<?php

namespace Pim\Bundle\CatalogBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;

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
     * @var ProductFormView
     */
    protected $productFormView;

    /**
     * @param ProductFormView $productFormView
     */
    public function __construct(ProductFormView $productFormView)
    {
        $this->productFormView = $productFormView;
    }

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
                'entity',
                array(
                    'choices' => $options['locales'],
                    'class'   => 'Pim\\Bundle\\CatalogBundle\\Entity\\Locale',
                    'attr'    => array(
                        'class' => 'operation-param',
                    )
                )
            )
            ->add(
                'attributesToDisplay',
                'entity',
                array(
                    'class' => 'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
                    'choices'  => $options['commonAttributes'],
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'group_by' => 'virtualGroup.name',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'data_class'       => 'Pim\\Bundle\\CatalogBundle\\MassEditAction\\EditCommonAttributes',
                'locales'          => array(),
                'commonAttributes' => array(),
            )
        );
    }

    public function getName()
    {
        return 'pim_catalog_mass_edit_common_attributes';
    }
}
