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

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Set product value as draft
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class DraftViewUpdater implements ViewUpdaterInterface
{
    /**
     * @param ProductDraftChangesApplier $applier
     * @param UrlGeneratorInterface      $urlGenerator
     */
    public function __construct(
        ProductDraftChangesApplier $applier,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->applier      = $applier;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributeView)
    {
        if ((isset($attributeView['value'])
            && $this->applier->isMarkedAsModified($attributeView))
        ) {
            $this->markFieldAsModified($attributeView['value']);
        } elseif (isset($attributeView['values'])) {
            foreach (array_keys($attributeView['values']) as $scope) {
                if ($this->applier->isMarkedAsModified($attributeView, $scope)) {
                    $this->markFieldAsModified($attributeView['values'][$scope]);
                }
            }
        }
    }
}
