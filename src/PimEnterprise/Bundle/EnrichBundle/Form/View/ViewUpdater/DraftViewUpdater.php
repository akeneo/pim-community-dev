<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * TEST
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class DraftViewUpdater implements ViewUpdaterInterface
{
    /**
     * @param UrlGeneratorInterface      $urlGenerator
     * @param ProductDraftChangesApplier $applier
     */
    public function __construct(
        ProductDraftChangesApplier $applier,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->applier      = $applier;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Mark a form view as modified
     *
     * We do it by inserting the product value id in a "modified" attribute of the form field
     * This is useful to later load the current product value data
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

    /**
     * Check if an attribute is a draft and mark it as a draft
     *
     * @param array  $views
     * @param string $key
     * @param string $name
     */
    protected function checkIfDraft(array $views, $key, $name)
    {
        if ((isset($views[$key]['attributes'][$name]['value'])
            && $this->applier->isMarkedAsModified($views[$key]['attributes'][$name]))
        ) {
            $this->markFieldAsModified($views[$key]['attributes'][$name]['value']);
        } elseif (isset($views[$key]['attributes'][$name]['values'])) {
            foreach (array_keys($views[$key]['attributes'][$name]['values']) as $scope) {
                if ($this->applier->isMarkedAsModified($views[$key]['attributes'][$name], $scope)) {
                    $this->markFieldAsModified($views[$key]['attributes'][$name]['values'][$scope]);
                }
            }
        }
    }

    /**
     * @param array  $views
     * @param string $key
     * @param string $name
     */
    public function update(array $views, $key, $name)
    {
        $this->checkIfDraft($views, $key, $name);
    }
}
