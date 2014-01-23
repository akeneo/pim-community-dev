<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BindAssociationTargetsSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\IgnoreMissingFieldDataSubscriber;

/**
 * Product edit form type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditType extends AbstractType
{
    /**
     * Storage of the product form fields in order to use its frontend manipulation
     *
     * @var ProductFormView $productFormView
     */
    protected $productFormView;

    /**
     * Constructor
     *
     * @param \Pim\Bundle\CatalogBundle\Form\View\ProductFormView $productFormView
     */
    public function __construct(ProductFormView $productFormView)
    {
        $this->productFormView = $productFormView;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_edit';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_product';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['enable_state']) {
            $builder->add('enabled', 'hidden');
        }
        $builder
            ->add(
                'associations',
                'collection',
                [
                    'type' => 'pim_catalog_association'
                ]
            )
            ->get('associations')
            ->addEventSubscriber(new BindAssociationTargetsSubscriber());

        if ($options['enable_family']) {
            $builder->add(
                'family',
                'entity',
                [
                    'class'       => 'PimCatalogBundle:Family',
                    'empty_value' => ''
                ]
            );
        }
        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                [
                    'class'    => 'PimCatalogBundle:Category',
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
                ]
            )
            ->addEventSubscriber(new IgnoreMissingFieldDataSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'enable_family' => true,
                'enable_state'  => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }
}
