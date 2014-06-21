<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\View;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormView;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\PropositionChangesApplier;

/**
 * Product form view decorator that adds classes and fields on which proposal value is applied
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class HighlightModifiedProductFormView implements ProductFormViewInterface
{
    /** @var ProductFormView */
    protected $productFormView;

    /** @var PropositionChangesApplier */
    protected $applier;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param ProductFormView           $productFormView
     * @param PropositionChangesApplier $applier
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        PropositionChangesApplier $applier,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->productFormView = $productFormView;
        $this->applier = $applier;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildren(ProductValueInterface $value, FormView $view)
    {
        $this->productFormView->addChildren($value, $view);
    }

    /**
     * Get the computed view
     *
     * @return array|FormView
     */
    public function getView()
    {
        $views = $this->productFormView->getView();

        foreach ($views as $key => $view) {
            foreach (array_keys($view['attributes']) as $name) {
                if (isset($views[$key]['attributes'][$name]['value'])
                    && $this->applier->isMarkedAsModified($views[$key]['attributes'][$name])) {

                    $this->markFieldAsModified($views[$key]['attributes'][$name]['value']);

                } elseif (isset($views[$key]['attributes'][$name]['values'])) {

                    foreach (array_keys($views[$key]['attributes'][$name]['values']) as $scope) {
                        if ($this->applier->isMarkedAsModified($views[$key]['attributes'][$name], $scope)) {
                            $this->markFieldAsModified($views[$key]['attributes'][$name]['values'][$scope]);
                        }
                    }

                }
            }
        }

        return $views;
    }

    /**
     * Mark a form view as modified
     *
     * We do it by inserting the product value id in a "modified" attribute of the form field
     * This is usefull to later load the current product value data
     *
     * @param FormView $view
     */
    protected function markFieldAsModified(FormView $view)
    {
        $value = $view->vars['value'];
        if (!$value instanceof AbstractProductValue) {
            return;
        }

        $url = $this->urlGenerator->generate(
            'pimee_enrich_product_value_show',
            [
                'productId' => $value->getEntity()->getId(),
                'attributeCode' => $value->getAttribute()->getCode(),
                'locale' => $value->getLocale(),
                'scope' => $value->getScope(),
            ]
        );

        foreach ($view as $child) {
            $child->vars['modified'] = $url;
        }
    }
}
