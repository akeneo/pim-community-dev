<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BindProductAssociationTargetsSubscriber;

/**
 * Description of ProductWebType
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class ProductWebType extends AbstractType
{
    /**
     * Storage of the product form fields in order to use its frontend manipulation
     *
     * @var ProductFormView $productFormView
     */
    protected $productFormView;
    
    /**
     * {@inheritdoc}
     */
    public function __construct(
        ProductFormView $productFormView
    ) {
        $this->productFormView = $productFormView;
    }
    public function getName()
    {
        return 'pim_product_web';
    }

    public function getParent()
    {
        return 'pim_product';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'productAssociations',
                'collection',
                array(
                    'type' => 'pim_catalog_product_association'
                )
            )
            ->get('productAssociations')
            ->addEventSubscriber(new BindProductAssociationTargetsSubscriber());

        if ($options['enable_family']) {
            $builder->add(
                'family',
                'entity',
                array(
                    'class'       => 'PimCatalogBundle:Family',
                    'empty_value' => ''
                )
            );
        }
        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                array(
                    'class'    => 'PimCatalogBundle:Category',
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
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
}
