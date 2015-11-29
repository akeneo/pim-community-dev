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

use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Set rule impacted attributes as smart attribute
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class SmartViewUpdater implements ViewUpdaterInterface
{
    /** @var RuleRelationManager */
    protected $ruleRelationManager;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param RuleRelationManager   $ruleRelationManager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        RuleRelationManager $ruleRelationManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->ruleRelationManager = $ruleRelationManager;
        $this->urlGenerator        = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributeView)
    {
        $this->checkIfSmartAttribute($attributeView);
    }

    /**
     * Mark an attribute as impacted by a rule
     *
     * @param FormView $view
     * @param int      $attributeId
     */
    protected function markAttributeAsSmart(FormView $view, $attributeId)
    {
        $value = $view->vars['value'];
        if (!$value instanceof ProductValueInterface) {
            return;
        }

        $url = $this->urlGenerator->generate(
            'pimee_catalog_rule_index',
            [
                'resourceId'   => $attributeId,
                'resourceName' => 'attribute',
            ]
        );

        foreach ($view as $child) {
            $child->vars['smart'] = $url;
        }
    }

    /**
     * Check if an attribute is smart or not
     *
     * @param FormView $attributeView
     */
    protected function checkIfSmartAttribute($attributeView)
    {
        if ((isset($attributeView['value'])
            && $this->ruleRelationManager->isAttributeImpacted($attributeView['id']))
        ) {
            $this->markAttributeAsSmart(
                $attributeView['value'],
                $attributeView['id']
            );
        } elseif (isset($attributeView['values'])) {
            foreach (array_keys($attributeView['values']) as $scope) {
                if ($this->ruleRelationManager->isAttributeImpacted($attributeView['id'])) {
                    $this->markAttributeAsSmart(
                        $attributeView['values'][$scope],
                        $attributeView['id']
                    );
                }
            }
        }
    }
}
